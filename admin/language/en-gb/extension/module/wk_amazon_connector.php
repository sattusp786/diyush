<?php
// Heading
$_['heading_title']   				= 'Opencart Amazon Connector';

// Text
$_['text_extension']  				= 'Extensions';
$_['text_success']    				= 'Success: You have modified Amazon connector module!';
$_['text_edit']       				= 'Edit Amazon connector Module';
$_['text_default']    				= 'Default Store';
$_['text_option1']    				= 'Option 1 : Import all Amazon products (with or without variation)';
$_['text_option2']    				= 'Option 2 : Import only those Amazon products which do not have any variation.';

// Entry Amazon
$_['entry_status']     				= 'Status';
$_['entry_default_category']	= 'Choose default category';
$_['entry_default_quantity']	= 'Default Product Quantity';
$_['entry_default_weight']		= 'Amazon Product Weight (in Gram)';
$_['entry_default_store']			= 'Default store for order sync';
$_['entry_order_status']			= 'Amazon imported order status';
$_['entry_default_product_store']	= 'Default store for product';
$_['entry_variation_options']	= 'Product Variation(Option) Choice Options';
$_['entry_update_imported']	  = 'Update Imported Products';
$_['entry_update_exported']	  = 'Update Exported Products';


//panel
$_['panel_general_options']   = 'General Options';
$_['panel_order_options']	    = 'Order Options';
$_['panel_product_options']	  = 'Product Options';
$_['panel_real_time_setting']	= 'Real Time Update Settings  ';


//help amazon
$_['help_default_category'] 	= 'Choose default Opencart category for assigning amazon product.';
$_['help_default_quantity']		= 'Given Quantity will be Amazon/Opencart default product quantity, If product quantity is zero.';
$_['help_default_weight']			= 'This value will be use when amazon product doesn\'t contain the weight.';
$_['help_default_store']			= 'Select opencart store for order sync.';
$_['help_order_status']				= 'Set default order status for order which imported from amazon';
$_['help_default_product_store']	= 'Selected store will be assigned to all amazon products by default';
$_['help_variation_options']	= 'You can select option for Product with/without variation/option.';
$_['info_option']             = 'Option 1 : In this case, A new product will always created in opecnart for each amazon product whether that product has variation/option OR not.<br><br>
Option 2 : In this case, Products will import only those have no variation/option. Products with variation/option will not import. In order import case, if order\'s product has variations(options) then product and order related to
that product both will not import..';
$_['entry_update_imported']	  = 'Update imported product on Amazon Store!';
$_['entry_update_exported']	  = 'Update exported product on Amazon Store!';
$_['help_update_imported']	  = 'Update imported product on Amazon store, if we do any update on an opencart store.';
$_['help_update_exported']	  = 'Update exported product on Amazon store, if we do any update on an opencart store.';
//placeholder
$_['placeholder_quantity']	  = 'Enter default product quantity..';
$_['placeholder_weight']			= 'Enter default product weight (in Gram)..';

$_['info_update_imported']    = 'Note: If imported product will update on Opencart Store, then only Quantity and Price of that product will automatically update on Amazon Store';
$_['info_update_exported']    = 'Note: If exported product will update on Opencart Store, then only Quantity and Price of that product will automatically update on Amazon Store';
// Error
$_['error_permission'] = 'Warning: You do not have permission to modify Amazon connector module!';
