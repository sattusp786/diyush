<?php

$_['heading_title_import'] 			= 'Import Products From Amazon Store';
$_['heading_title_export'] 			= 'Export Products To Amazon Store';
$_['heading_title'] 				    = 'Product Synchronization';


$_['info_about_import_tab'] 	 = 'Information About This Tab';
$_['info_about_export_tab'] 	 = 'Be sure before adding product to Amazon store';
$_['sync_product_tab'] 				 = 'Synchronize Amazon Products';
$_['export_product_action'] 	 = 'Action For Export Product To Amazon';
$_['update_delete_export'] 	   = 'Update Or Delete Exported Product To Amazon';
$_['info_report_id'] 				   = 'Before synchronizing amazon products, you must generate a Report-Id.';

$_['text_product_list']				 = 'Imported Amazon Product List';
$_['text_no_results']				   = 'No record found!';
$_['text_tab_info1']				   = 'This tab imports amazon products to opencart.';
$_['text_tab_info2']				   = 'Before importing amazon products, first it will import associated category to opencart.';
$_['text_tab_info3']				   = 'If product is already synchronized then it will update product..';
$_['text_export_tab_info1']		 = 'Only enabled products of Opencart store will be exported to Amazon Store.';
$_['text_export_tab_info2']		 = 'For exporting products from Opencart to Amazon, product must have ASIN number.';
$_['text_export_tab_info3']		 = 'When you update any exported product, then only product price and quantity will update on Amazon Store.';
$_['text_export_tab_info4']		 = 'Amazon seller product ASIN will be mapped with Opencart product Id.';
$_['text_export_tab_info5']		 = 'If product has combination/variation on Opencart, then each combination/variation will be exported as individual product on Amazon.';
$_['success_report_added']		 = 'Success: Report Id: %s generated successfully. Now you can import products from Amazon!';
$_['success_report_order_added']= 'Success: Report Id: %s generated successfully. Now you can import orders from Amazon!';

$_['success_export_to_amazon'] = 'Success : Opencart product Name %s exported to amazon store successfully with Id: ';
$_['success_update_export_to_amazon']= 'Success : Opencart product Name %s updated to amazon store successfully with Id: ';
$_['success_delete_export_to_amazon']= 'Success : Opencart product Name %s deleted from amazon store successfully!';
$_['success_get_amazon_product']	  = 'Success: Total %s products found!';
$_['text_success_delete']           = 'Success: Amazon Product(Id- %s) map entry deleted successfully for Opencart Product-Id #';
$_['text_processing']				        = 'Processing...';
$_['text_success_import_product']   = 'Success: %s products saved successfully!';
$_['text_success_product_delete']   = 'Success: mapped product entry deleted successfully!';
$_['text_variation_list']           = 'Variation List';
$_['text_select_action']            = '-- Select Action --';
$_['text_action_all_products']      = 'All Opencart Products';
$_['text_action_combination']       = 'Selected Opencart Product Combination/Variation';
$_['text_all_exported_products']    = 'All Exported Products';
$_['text_selected_exported_product']= 'Selected Exported Product';
$_['text_confirm']                  = 'Confirm: Do you want to delete mapped product?';


$_['text_product_asin']             = 'Amazon Product ASIN';
$_['info_product_asin_sync']        = 'Note: If ASIN Product is already synchronized, then your product will update!';
$_['help_select_combination']       = 'You can select at least one product combination for export to amazon.';
$_['info_button_export_start'] 			= 'Start Adding Product To Amazon';
$_['info_button_update_export'] 		= 'Update Product(s) To Amazon';
$_['info_button_delete_export'] 		= 'Delete Product(s) To Amazon';


$_['entry_product_response']        = 'Product Synchronization Result';
$_['entry_product_asin']            = 'Enter amazon product ASIN here...';
$_['entry_select_product_option']   = 'Select Product Export Option';
$_['entry_select_combination']      = 'Select Product Combination/Variation';
$_['entry_select_exported']         = 'Select Exported Product';

$_['button_import_amazon_product']	= 'Import Product From Amazon';
$_['button_report_id']				      = 'Generate Report Id';
$_['button_import_product']			    = 'Import/Update Amazon Store Products';
$_['button_import_product_by_asin']	= 'Import/Update Amazon Store Products By ASIN';
$_['button_import']                 = 'Import Products From Amazon';
$_['button_export']                 = 'Export Products To Amazon';
$_['button_delete']					        = 'Delete Product';
$_['button_back'] 					        = 'Back To Product List';
$_['button_import'] 					      = 'Import Product';
$_['button_export_start'] 					= 'Export Product';
$_['button_update_export'] 					= 'Update Exported Product';
$_['button_delete_export'] 					= 'Delete Exported Product';
$_['button_close']   					      = 'Close';
$_['button_delete_product']		      = 'Delete Product';
$_['button_delete_product_info']    = 'Delete Imported Product';

$_['column_map_id']					        = 'Map ID';
$_['column_oc_product_id']		      = 'Opencart ProductID';
$_['column_product_name']			      = 'Product Name';
$_['column_amazon_product_asin']    = 'Amazon Product ASIN';
$_['column_price']		              = 'Price';
$_['column_quantity']		            = 'Quantity';
$_['column_sync_source']		        = 'Sync Source';
$_['column_action']		              = 'Action';

$_['error_no_item_found'] 		      = 'Warning: There is no item found to save!';
$_['error_no_account_details'] 	    = 'Warning: Please check for Amazon module configration details!';
$_['error_category_not_mapped']     = 'Warning: Please map %s Amazon category with opencart category!';
$_['error_permission'] 				      = 'Warning: You don\'t have permission to delete product!';
$_['error_export_option'] 				  = 'Warning: You have to select at least one option to export opencart product to amazon store!';
$_['error_update_delete_export'] 		= 'Warning: You have to select at least one option to update/delete exported  product to amazon store!';
$_['error_account_not_exist'] 		  = 'Warning: Account inforamtion is not correct!';
$_['error_report_id'] 				      = 'Warning: Unable to generate product report id, try again!';
$_['error_report_list_id'] 			    = 'Warning: Product Report Id generated, but unable to generate product report list id, Try again!';
$_['error_generate_report_first'] 	= 'Warning: You have to generate product report id first, before importing Amazon product!';
$_['error_no_product_found'] 		    = 'Warning: No product found at Amazon store!';
$_['error_wrong_asinformat']			  = 'Warning: Provide correct ASIN code for Amazon product!';
$_['error_not_referesh']			      = 'Warning: Do not refresh/back the page until the process is completed!';
$_['error_wrong_selection']			    = 'Warning: Something went wrong, refresh the page and try again!';

$_['error_no_product_found']			  = 'Warning: There is no product found to export to amazon store!';
$_['error_occurs']			            = 'Warning: Some issues occured while export products to amazon store!';
$_['error_export_to_amazon']        = 'Warning: Opencart product Name %s failed to export to amazon store!';
$_['error_update_export_to_amazon'] = 'Warning: Opencart product Name %s failed to update to amazon store, check for Unique Identification Number!';
$_['error_found_order1']            = 'Warning: You can\'t delete product named %s ,';
$_['error_found_order2']            = 'this product is related to Amazon Order-Id: %s';
