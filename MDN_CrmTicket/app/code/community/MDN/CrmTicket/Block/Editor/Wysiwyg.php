<?php

class MDN_CrmTicket_Block_Editor_Wysiwyg extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * return wysiwyg html control
     * 
     * @param type $name
     * @return type 
     */
    public function getWysiwygControl($name, $content, $required = false, $width = '100%', $height = '400px') {

        try {
            $form = new Varien_Data_Form(array());

            //http://www.pixafy.com/blog/2013/06/overcoming-magentos-wysiwyg-part-2-customizing-tinymce-settings
            $config = array();
            $config['document_base_url'] = $this->getData('store_media_url');
            $config['store_id'] = 0;
            $config['add_variables'] = false;
            $config['add_widgets'] = false;
            $config['add_directives'] = false;
            $config['use_container'] = false;
            $config['add_images'] = false;
            $config['container_class'] = 'hor-scroll';
            

            //Change the writing mode
            /*$config['force_br_newlines'] = true;
            $config['force_p_newlines'] = false;
            $config['convert_newlines_to_brs'] = true;
            $config['format'] = 'preformatted';*/
            
            $mageversion = Mage::getVersion();
            $t = explode('.', $mageversion);
            $version= $t[0].'.'.$t[1];

            $style= 'width:'.$width.';height:'.$height;
            
            if($version < 1.3){
              $form->addField($name, 'editor', array(
                  'name' => $name,
                  'id' => $name,
                  'style' => $style,
                  'required' => $required,
                  'force_load' => true,
                  'wysiwyg'   => true,
                  'value' => $content
              ));
            }else{
              $form->addField($name, 'editor', array(
                  'name' => $name,
                  'id' => $name,
                  'style' => $style,
                  'required' => $required,
                  'force_load' => true,
                  'config' => Mage::getSingleton('cms/wysiwyg_config')->getConfig($config),
                  'value' => $content
              ));
            }

            return $form->toHtml();
        } catch (Exception $ex) {
            //if exception, return the regular textarea
            $html = '<textarea name="'.$name.'" id="'.$name.'" cols="200" rows="20">'.$content.'</textarea>';
            $html .= '<br><i>No wysiwyg component found</i>';
            return $html;
        }
    }

}

