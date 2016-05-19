var packing = Class.create();
packing.prototype = {

    //**********************************************************************************************************************************
    //initialize object
    initialize: function(orderInformationUrl){
        this.orderInformationUrl = orderInformationUrl;
        this.orderId = null;
        this.products = null;
    },

    //**********************************************************************************************************************************
    //
    waitForScan: function()
    {
        objPacking.showInstruction(objPacking.translate('Scan order to Pack'), false);

        document.onkeypress = handleKey;
        enableCatchKeys(null, 'objPacking.scanOrderBarcode();', 'objPacking.barcodeDigitScanned();');

    },


    //**********************************************************************************************************************************
    //
    barcodeDigitScanned:function()
    {
        objPacking.showMessage(KC_value);
    },


    //******************************************************************************
    //
    showMessage: function(text, error)
    {
        if (text == '')
            text = '&nbsp;';

        if (error)
            text = '<font color="red">' + text + '</font>';
        else
            text = '<font color="green">' + text + '</font>';

        document.getElementById('div_message').innerHTML = text;
        document.getElementById('div_message').style.display = '';
    },

    //******************************************************************************
    //
    hideMessage: function()
    {
        document.getElementById('div_message').style.display = 'none';
    },


    //******************************************************************************
    //display instruction for current
    showInstruction: function(text)
    {
        document.getElementById('div_instruction').innerHTML = text;
        document.getElementById('div_instruction').style.display = '';
    },

    //******************************************************************************
    //
    hideInstruction: function()
    {
        document.getElementById('div_instruction').style.display = 'none';
    },

    //******************************************************************************
    //
    scanOrderBarcode: function()
    {
        //init vars
        var barcode = KC_value;
        KC_value = '';
        var url = objPacking.orderInformationUrl;
        url += 'barcode/' + barcode;

        //ajax request
        var request = new Ajax.Request(
            url,
            {
                method: 'GET',
                evalScripts: true,
                onSuccess: function onSuccess(transport)
                {
                    elementValues = eval('(' + transport.responseText + ')');
                    if (elementValues['error'] == true)
                    {
                        objPacking.showMessage(elementValues['message'], true);
                    }
                    else
                    {
                        //display order information
                        objPacking.hideMessage();
                        objPacking.showInstruction(objPacking.translate('Please scan products'), false);
                        document.getElementById('div_main').innerHTML = elementValues['order_html'];

                        //init datas
                        objPacking.orderId = elementValues['order_id'];
                        objPacking.products = elementValues['products_json'];

                        document.onkeypress = handleKey;
                        enableCatchKeys(null, 'objPacking.scanProductBarcode();', 'objPacking.barcodeDigitScanned();');

                        document.getElementById('div_main').style.display = '';

                        showMessage('');

                    }

                },
                onFailure: function onFailure(transport)
                {
                    objPacking.showMessage(objPacking.translate('An error occured'), true);
                }
            }
            );
    },


    //******************************************************************************
    //
    scanProductBarcode: function()
    {
        //init vars
        var barcode = KC_value;
        KC_value = '';

        //get product id
        var productInformation = objPacking.getProduct(barcode);
        if (productInformation == null)
            objPacking.showMessage(objPacking.translate('Unknown barcode ') + barcode, true);
        else
        {
            //check qty
            if (productInformation.qty_scanned == productInformation.qty)
            {
                objPacking.showMessage(objPacking.translate('Product quantity already scanned !'), true);
                return false;
            }

            //increment qty
            objPacking.showMessage(productInformation.name + objPacking.translate(' scanned'));
            productInformation.qty_scanned += 1;
            objPacking.updateQuantities();
        }
    },
    
    decreaseProduct: function (productId)
    {
        //get product
        productInformation = objPacking.getProductFromId(productId);
        
        //update qty
        if (productInformation.qty_scanned > 0)
        {
            objPacking.showMessage(productInformation.name + objPacking.translate(' scanned'));
            productInformation.qty_scanned -= 1;
            objPacking.updateQuantities();
        }
    },

    increaseProduct: function (productId)
    {
        //get product
        productInformation = objPacking.getProductFromId(productId);
        
        //update qty
        if (productInformation.qty_scanned < productInformation.qty)
        {
            objPacking.showMessage(productInformation.name + objPacking.translate(' scanned'));
            productInformation.qty_scanned += 1;
            objPacking.updateQuantities();
        }
    },
    
    //******************************************************************************
    //
    getProduct: function (barcode)
    {
        var i;
        var productInformation = null;
        for(i=0;i<this.products.length;i++)
        {
            if (this.products[i].barcode == barcode)
            {
                productInformation = this.products[i];
            }
        }
        return productInformation;
    },
    
    //******************************************************************************
    //
    getProductFromId: function (productId)
    {
        var i;
        var productInformation = null;
        for(i=0;i<this.products.length;i++)
        {
            if (this.products[i].id == productId)
            {
                productInformation = this.products[i];
            }
        }
        return productInformation;
    },
    
    //******************************************************************************
    //
    updateQuantities: function()
    {
        var i;
        var productInformation = null;
        for(i=0;i<this.products.length;i++)
        {
            productInformation = this.products[i];
            document.getElementById('qty_scanned_' + productInformation.id).innerHTML = productInformation.qty_scanned;

            var remainingQty = (productInformation.qty - productInformation.qty_scanned);
            var color = 'red';
            if (remainingQty == 0)
            {
                color = 'green';
                document.getElementById('checked_' + productInformation.id).src = checkedImageUrl;
            }
            document.getElementById('qty_to_scan_' + productInformation.id).innerHTML =  '<font color="' + color + '">' + remainingQty + '</font>';
        }
    },

    //******************************************************************************
    //
    commit: function(askForWeight, weight)
    {
        //check that all products have been scanned in the requested qty
        var productInformation = null;
        for(i=0;i<this.products.length;i++)
        {
            productInformation = this.products[i];
            var remainingQty = (productInformation.qty - productInformation.qty_scanned);
            if (remainingQty > 0)
            {
                objPacking.showMessage(remainingQty + ' ' + productInformation.name + objPacking.translate(' are missing !'), true);
                return false
            }
        }
        
        //ask the weight
        if (askForWeight)
        {
            weight = prompt('Please confirm the weight', weight);
            if (!weight)
                return false;
        }
        
        //submit form in ajax
        document.getElementById('order_id').value = objPacking.orderId;
        document.getElementById('weight').value = weight;
        document.getElementById('frm_packing').submit();

    },

    //******************************************************************************
    //
    cancel: function()
    {
        document.location.href = document.location.href;
    },
    
    //******************************************************************************
    //
    translate: function(text) {
        try {
            if(Translator){
               return Translator.translate(text);
            }
        }
        catch(e){}
        return text;
    }
}