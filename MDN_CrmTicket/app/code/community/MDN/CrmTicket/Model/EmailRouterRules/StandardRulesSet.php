<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2013 BoostMyshop (http://www.boostmyshop.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_CrmTicket
 * @version 1.2
 */
class MDN_CrmTicket_Model_EmailRouterRules_StandardRulesSet extends Mage_Core_Model_Abstract {

    const RULE_NOT_CHECKED = 0;
    const RULE_PRESENT = 1;
    const RULE_VALID = 2;
    const RULE_INVALID = 3;
    
    /**
     * Entry point
     *
     * Try to apply active routing rules to ticket depending of the email recieved
     *
     * @param type $email
     * @param type $ticket
     */
    public function updateTicketUsingRules($email, $ticket)
    {
      $rulesToExecute = $this->getRulesToExecuteByPriority($email);
      return $this->executeRulesOnTicket($rulesToExecute, $ticket);
    }

    protected function executeRulesOnTicket($rulesToExecute, $ticket){

      $nbUpdate = 0;
      $routerLog = '';

      if($rulesToExecute){
        $routerLog .= "\n".'Nb rules '.count($rulesToExecute);
        $updated = false;
        foreach($rulesToExecute as $rule){
          
          $routerLog .= "\n".'Using rule : '.$rule->getcerr_name()." for tid=".$ticket->getId();
          //$routerLog .= "\n".'Rule detail '.Mage::helper('CrmTicket/String')->getVarDumpInString($rule);

          $storeId = $rule->getcerr_store_id();
          if($storeId != 0){
            $ticket->setct_store_id($storeId);
            $routerLog .= "\n"."Store changed to ".$storeId;
            $updated = true;
            $nbUpdate ++;
          }

          $managerId = $rule->getcerr_manager_id();
          if($managerId != 0){
            $ticket->setct_manager($managerId);
            $routerLog .= "\n"."Manager changed to ".$managerId;
            $updated = true;
            $nbUpdate ++;
          }

          $statusLabel = $rule->getcerr_status();
          if(strlen($statusLabel)>0){
            if($statusLabel != "0"){
              $ticket->setct_status($statusLabel);
              $routerLog .= "\n"."Status changed to ".$statusLabel;
              $updated = true;
              $nbUpdate ++;
            }            
          }

          $categoryId = $rule->getcerr_category_id();
          if($categoryId != 0){
            $ticket->setct_category_id($categoryId);
            $routerLog .= "\n"."Category changed to ".$categoryId;
            $updated = true;
            $nbUpdate ++;
          }

          $tagId = $rule->getcerr_tag_id();
          if($tagId != 0){
            mage::helper('CrmTicket/Tag')->addTicketTag($tagId,$ticket->getId());
            $routerLog .= "\n"."Tag added to ".$tagId;
            $updated = true;
            $nbUpdate ++;
          }
        }

        if($updated){
            $ticket->save();
            $routerLog .= "\n"."ticket#".$ticket->getId()." updated for $nbUpdate elements";
            $ticket2 = mage::getModel('CrmTicket/Ticket')->load($ticket->getId());
            $routerLog .= "\n"."ticket status after".$ticket2->getct_status()." updated for $nbUpdate elements";
        }
      }
      /*else{
         $routerLog .= "\n"."NO rules founds";
      }*/
      
      if($routerLog){           
            mage::log($routerLog, null, 'crm_ticket_mail_import_router_rules.log');
      }

      return $nbUpdate;
    }


    protected function getRulesToExecuteByPriority($email){

      $rulesIdsToExecute = $this->getMatchingRules($email->to, $email->fromEmail, $email->subject, $email->response);
      
      $rulesToExecuteByPriorityCount = count($rulesIdsToExecute);

      $collection = null;

      if($rulesToExecuteByPriorityCount>0){

        $collection = Mage::getModel('CrmTicket/EmailRouterRules')
                  ->getCollection()
                  ->addFieldToFilter('cerr_id', array("in" => $rulesIdsToExecute))
                  ->setOrder('cerr_priority', 'desc');//desc, because rule of priority 1 have to be executed after rules of priority 2
                  
        //$rulesToExecuteByPriorityList .= $collection->getSelect();
        //TODO add a second criteria when 2 rules have the same priority
      }

      return $collection;
    }



    /**
     * Select the rule to that have at least 1 criteria to check
     *
     * @param type $emailAccount
     * @param type $fromEmail
     * @param type $subject
     * @param type $body
     * @return type
     */
    private function getMatchingRules($emailAccount, $fromEmail, $subject, $body){

        $rulesIdsToExecute = array();

        //get all rule staht match at least 1 criteria
        $rulesIdsToExecute = $this->getAllMatchingRules($emailAccount, $fromEmail, $subject, $body);

        //do some &
        $rulesIdsToExecute = $this->checkIfRulesMatchAllCriterias($rulesIdsToExecute, $emailAccount, $fromEmail, $subject, $body);

        //return a list of rule to checks
        return $rulesIdsToExecute;
    }


    private function getAllMatchingRules($emailAccount, $fromEmail, $subject, $body){

        $rulesIdsToExecute = array();

        //Checks the "Email account"
        $rulesIdsToExecute = array_merge($rulesIdsToExecute,  $this->getActivesRulesByEmailAccount($emailAccount));

        //Checks the "From"
        $rulesIdsToExecute = array_merge($rulesIdsToExecute, $this->getActivesRulesByFromPattern($fromEmail));

        //Checks the "Subject"
        $rulesIdsToExecute = array_merge($rulesIdsToExecute, $this->getActivesRulesBySubjectPattern($subject));

        //Checks the "Body"
        $rulesIdsToExecute = array_merge($rulesIdsToExecute, $this->getActivesRulesByBodyPattern($body));

        //return a list of rule to checks
        return $rulesIdsToExecute;
    }

    private function checkIfRulesMatchAllCriterias($rulesIdsToExecute,$emailAccount, $fromEmail, $subject, $body){

        $restrictedRulesIdsToExecute = array();

        if(count($rulesIdsToExecute)>0){
            foreach($rulesIdsToExecute as $ruleId){
                if($this->checksIfRuleMatchWithAllRuleCriterias($ruleId, $emailAccount, $fromEmail, $subject, $body)){
                    $restrictedRulesIdsToExecute[] = $ruleId;
                }
            }
        }


        return $restrictedRulesIdsToExecute;
    }

    private function checksIfRuleMatchWithAllRuleCriterias($ruleId, $emailAccount, $fromEmail, $subject, $body){

        $rule = Mage::getModel('CrmTicket/EmailRouterRules')->load($ruleId);

        //Email Account
        if($rule->getcerr_email_account_id()){

            $emailAccount = Mage::getModel('CrmTicket/EmailAccount')->getEmailAccountByLogin(trim($emailAccount));
            if($emailAccount){
                if(!($emailAccount->getcea_id() == $rule->getcerr_email_account_id())){
                  return false;
                }
            }
        }

        if($rule->getcerr_from_pattern()){
            if (!preg_match(Mage::helper('CrmTicket/String')->partenize($rule->getcerr_from_pattern()), $fromEmail)){
               return false;
            }
        }

        if($rule->getcerr_subject_pattern()){
            if (!preg_match(Mage::helper('CrmTicket/String')->partenize($rule->getcerr_subject_pattern()), $subject)){
               return false;
            }
        }

        if($rule->getcerr_body_pattern()){
            if (!preg_match(Mage::helper('CrmTicket/String')->partenize($rule->getcerr_body_pattern()), $body)){
               return false;
            }
        }

        

        

        return true;
    }



    private function getActivesRulesByBodyPattern($body){

        $rulesId = array();
      
        $body = Mage::helper('CrmTicket/String')->normalize($body);
        
        if($body){
          $collection = Mage::getModel('CrmTicket/EmailRouterRules')
                ->getCollection()
                ->addFieldToFilter('cerr_body_pattern', array("neq" => ''))
                ->addFieldToFilter('cerr_active', MDN_CrmTicket_Model_EmailRouterRules::ACTIVE_RULE);

          foreach($collection as $rule) {


              $pattern = Mage::helper('CrmTicket/String')->partenize($rule->getcerr_body_pattern());

              //echo "<br>pattern to check :".$pattern;

              if (preg_match($pattern, $body))
              {
                  $rulesId[] = $rule->getId();
              }
          }
        }

        return $rulesId;
    }

    private function getActivesRulesBySubjectPattern($subject){

        $rulesId = array();

        $subject = Mage::helper('CrmTicket/String')->prepareFieldforComparison($subject);

        if($subject){
          $collection = Mage::getModel('CrmTicket/EmailRouterRules')
                ->getCollection()
                ->addFieldToFilter('cerr_subject_pattern', array("neq" => ''))
                ->addFieldToFilter('cerr_active', MDN_CrmTicket_Model_EmailRouterRules::ACTIVE_RULE);

          foreach($collection as $rule) {
                          
              $pattern = Mage::helper('CrmTicket/String')->partenize($rule->getcerr_subject_pattern());

              if (preg_match($pattern, $subject))
              {
                  $rulesId[] = $rule->getId();
              }
          }
        }

        return $rulesId;
    }

    private function getActivesRulesByFromPattern($fromEmail){

        $rulesId = array();

        //echo "<br>from email to test $fromEmail";

        //1st we check email account
        if($fromEmail){
          $collection = Mage::getModel('CrmTicket/EmailRouterRules')
                ->getCollection()
                ->addFieldToFilter('cerr_from_pattern', array("neq" => ''))
                ->addFieldToFilter('cerr_active', MDN_CrmTicket_Model_EmailRouterRules::ACTIVE_RULE);

            foreach($collection as $rule) {
          
              $pattern = Mage::helper('CrmTicket/String')->partenize($rule->getcerr_from_pattern());

              if (preg_match($pattern, $fromEmail))
              {
                  $rulesId[] = $rule->getId();
              }
            }
        }

        return $rulesId;
    }



    private function getActivesRulesByEmailAccount($emailAccountEmail){

        $rulesId = array();
        

        //1st we check email account
        if($emailAccountEmail){
          $emailAccount = Mage::getModel('CrmTicket/EmailAccount')->getEmailAccountByLogin(trim($emailAccountEmail));

          if($emailAccount){
            $collection = Mage::getModel('CrmTicket/EmailRouterRules')
                  ->getCollection()
                  ->addFieldToFilter('cerr_email_account_id', $emailAccount->getcea_id())
                  ->addFieldToFilter('cerr_active', MDN_CrmTicket_Model_EmailRouterRules::ACTIVE_RULE);

              foreach($collection as $rule){
                $rulesId[] = $rule->getId();
              }
          }
        }
        

        return $rulesId;
    }



}