<?php
/**
 * @version [2.3.x.x] [Supported opencart version 2.3.x.x]
 * @category Webkul
 * @package Opencart-Amazon Connector
 * @author [Webkul] <[<http://webkul.com/>]>
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
class Amazonconnector {

	/*
	contain product information
	 */
	public $product = [];

	/*
	contain feed type of product
	 */
	public $feedType;


	public function __construct($registry) {
        $this->registry = $registry;
				$this->config 	= $registry->get('config');
        $this->currency = $registry->get('currency');
        $this->cache 	= $registry->get('cache');
				$this->db 		= $registry->get('db');
				$this->request 	= $registry->get('request');
				$this->session 	= $registry->get('session');
	}

    /**
     * [getListMarketplaceParticipations to get the marketplace participations list]
     * @param  array  $data [description]
     * @return [type]       [description]
     */
    public function getListMarketplaceParticipations($data = array()){
        $results = array();
        $accountDetails = array(
                        'Action'                            => 'ListMarketplaceParticipations',
                        'wk_amazon_connector_access_key_id' => $data['wk_amazon_connector_access_key_id'],
                        'wk_amazon_connector_seller_id'     => $data['wk_amazon_connector_seller_id'],
                        'wk_amazon_connector_secret_key'    => $data['wk_amazon_connector_secret_key'],
                        'wk_amazon_connector_country'       => $data['wk_amazon_connector_country'],
                        );

        $returnData = $this->checkConnection($accountDetails, $XMLdata = true);

        if(isset($returnData->ListMarketplaceParticipationsResult->ListMarketplaces->Marketplace)){
            $results['currency_code'] = $returnData->ListMarketplaceParticipationsResult->ListMarketplaces->Marketplace->DefaultCurrencyCode;
        }else{
            $results['error'] = true;
        }
       return $results;
    }

    /**
     * [checkConnection to check the connection with the amazon site based on country code]
     * @param  [type]  $data    [description]
     * @param  boolean $XMLdata [description]
     * @return [type]           [description]
     */
    public function checkConnection($data, $XMLdata = true)
    {
        $connectionDetails = array();
        $connectionDetails['Action']         = $data['Action'];
        $connectionDetails["Version"]        = "2011-07-01";
        $connectionDetails["Timestamp"]      = gmdate("Y-m-d\TH:i:s\Z");
        $connectionDetails['SignatureVersion'] = '2';
        $connectionDetails['SignatureMethod']= 'HmacSHA256';
        $connectionDetails["AWSAccessKeyId"] = $data['wk_amazon_connector_access_key_id'];
        $connectionDetails['SellerId']       = $data['wk_amazon_connector_seller_id'];
        $connectionDetails['Secretkey']      = $data['wk_amazon_connector_secret_key'];
        $connectionDetails['CountryCode']    = $data['wk_amazon_connector_country'];
        ksort($connectionDetails);

        $curlOptions = $this->setCurlOptions($connectionDetails);
        $curlHandle = curl_init();
        curl_setopt_array($curlHandle, $curlOptions);
				$curlResult = curl_exec($curlHandle);

        if (($curlResult = curl_exec($curlHandle)) == false) {
            $res = false;
        } else {
            $res = true;
        }

        if (!$res) {
            return (false);
        }

        curl_close($curlHandle);

        if ($XMLdata) {
            try {
                $resultXML = new \SimpleXMLElement($curlResult);
                return ($resultXML);
            } catch (\Exception $e) {
                return false;
            }
        } else {
            return ($curlResult);
        }
    }

    /**
     * [setCurlOptions make the amazon call url]
     * @param array $data [description]
     */
    public function setCurlOptions($data = array())
    {
        $type = '/' . trim('Sellers');
        $URL = $this->getAmazonURL($data['CountryCode']);
        $canonicalizedData = [];
        foreach ($data as $datakey => $dataval) {
            if(($datakey != 'CountryCode') && ($datakey != 'Secretkey')){
                $datakey = str_replace("%7E", "~", rawurlencode($datakey));
                $dataval = str_replace("%7E", "~", rawurlencode($dataval));
                $canonicalizedData[] = $datakey . "=" . $dataval;
            }
        }
        $canonicalizedData = implode("&", $canonicalizedData);
        $signString = "POST"."\n".$URL."\n".$type."\n".$canonicalizedData;

        $pacSignature = base64_encode(hash_hmac("sha256", $signString, $data['Secretkey'], true));
        $pacSignature = str_replace("%7E", "~", rawurlencode($pacSignature));

        $curlOptions = [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
        ];

        $curlOptions[CURLOPT_URL] = "https://" . $URL . $type;
        $curlOptions[CURLOPT_POSTFIELDS] = $canonicalizedData . "&Signature=" . $pacSignature;
        $curlOptions[CURLOPT_SSL_VERIFYPEER] = false;
        $curlOptions[CURLOPT_VERBOSE] = false;

        return $curlOptions;
    }

     /**
     * [getAmazonURL - get URL by country iso]
     * @return [type] [description]
     */
    public function getAmazonURL($accountCountryCode = false)
    {
        if ($accountCountryCode == 'US') {
            return ('mws.amazonservices.com');
        } elseif ($accountCountryCode == 'CA') {
            //return ('mws.amazonservices.ca');
            return ('mws.amazonservices.com');
        } elseif ($accountCountryCode == 'JP') {
            return ('mws.amazonservices.jp');
        } elseif ($accountCountryCode == 'MX') {
            //return ('mws.amazonservices.com.mx');
            return ('mws.amazonservices.com');
        } elseif ($accountCountryCode == 'CN') {
            return ('mws.amazonservices.com.cn');
        } elseif ($accountCountryCode == 'IN') {
            return ('mws.amazonservices.in');
        } elseif ($accountCountryCode == 'DE' || $accountCountryCode == 'ES' || $accountCountryCode == 'FR' || $accountCountryCode == 'IT' || $accountCountryCode == 'UK') {
            return ('mws-eu.amazonservices.com');
        } else {
            return false;
        }
    }

    /**
     * [loadFile to load amazon mws api file to execute functoinality]
     * @param  [string] $type     [file type Ex order,product etc]
     * @param  [string] $filename [name of the file that need to be loaded]
     */
    public function loadFile($type, $filename)
    {
        $path = DIR_APPLICATION.'../Lib'.'/amazon/';
        include_once($path.'amazon'.$type.'/src/MarketplaceWebService'.$type.'/Samples/'.$filename.'.php');
    }

    public function getReport($generatedReportId, $accountId = false)
    {
        $this->loadFile('', 'GetReportSample');
        $config             = $this->_getAccountConfigData($accountId);
        $config['reportId'] = $generatedReportId;
        $Getreport          = new \Getreport($config);
        $report = $Getreport->invokeGetReport($Getreport->service, $Getreport->request);
        return $report;
    }

    /**
     * [requestReport to get request id before sync product from amazon]
     * @param  [type]  $reportType [description]
     * @param  boolean $accountId  [description]
     * @return [type]              [description]
     */
    public function requestReport($reportType, $accountId = false)
    {
        $this->loadFile('', 'RequestReportSample');
        $config                 = $this->_getAccountConfigData($accountId);
        $config['reportType']   = $reportType;
        $requestReport          = new \Requestreport($config);
        $request                = $requestReport->invokeRequestReport($requestReport->service, $requestReport->request);
        return $request;
    }
    /**
     * [GetReportRequestList to get report request list id based on report id]
     * @param [type] $requestId [description]
     * @param [type] $accountId [description]
     */
    public function GetReportRequestList($requestId, $accountId)
    {
        $this->loadFile('', 'GetReportRequestListSample');
        $config = $this->_getAccountConfigData($accountId);
        $config['requestId'] = $requestId;
        $requestReport = new \GetReportRequestList($config);
        $request = $requestReport->invokeGetReportRequestList($requestReport->service, $requestReport->request);
        return $request;
    }
    /**
     * [getReportFinal generate the product list]
     * @param  [type]  $productGeneratedReportId [description]
     * @param  boolean $accountId                [description]
     * @return [type]                            [description]
     */
    public function getReportFinal($productGeneratedReportId, $accountId = false){
        if ($productGeneratedReportId) {
            $getReportHeading = $getReportData = $finalReport = array();

            $getReportContent = $this->getReport($productGeneratedReportId, $accountId);

            if ($getReportContent != '') {
                /**
                 * [$reportArray convert string to array (seperate with next-line)]
                 * @var [type]
                 */
                $reportArray = preg_split("/[\n]/", $getReportContent);

                if ($reportArray) {
                    /**
                     * [$getReportHeading get heading cloumn name from report text]
                     * @var [type]
                     */
                    $getReportHeading = preg_split("/[\t]/", $reportArray[0]);
                    $getReportData    = array_slice($reportArray, 1);

                    foreach ($getReportData as $key => $amazon_product) {
                        if (isset($amazon_product) && !empty($amazon_product)) {

                            $reportDataArray = preg_split("/[\t]/", $amazon_product);
                            if ($reportDataArray) {
                                for ($i = 0; $i < count($getReportHeading); $i++) {
                                    if ($getReportHeading[$i] == 'item-name' || $getReportHeading[$i] == 'item-description') {
                                        $finalReport[$key][$getReportHeading[$i]] = json_encode($reportDataArray[$i], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
                                    } else {
                                        $finalReport[$key][$getReportHeading[$i]] = $reportDataArray[$i];
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return $finalReport;
        }
    }

	/**
	 * [_getAccountConfigData to get module config setting]
	 * @param  boolean $amazon_AccountId [amazon account id]
	 * @return [type]                  [array of config data]
	 */
	public function _getAccountConfigData($amazon_AccountId = false)
	{
		$results = false;
		if($amazon_AccountId){
			if($accountInfo = $this->getAccountDetails(array('account_id' => $amazon_AccountId))){
				if($countryDetails = $this->getAmazonServiceUrlAndMarketplaceId($accountInfo['wk_amazon_connector_country'])){
					$results = [
			                'sellerId'          	=> $accountInfo['wk_amazon_connector_seller_id'],
			                'marketplaceId'     	=> $accountInfo['wk_amazon_connector_marketplace_id'],
			                'accessKeyId'       	=> $accountInfo['wk_amazon_connector_access_key_id'],
			                'secretKey'         	=> $accountInfo['wk_amazon_connector_secret_key'],
			                'applicationName'   	=> 'testmarket',
			                'applicationVersion'	=> '1.0.0',
			                'countryMarketplaceId'=> $countryDetails['marketplaceId'],
			                'serviceUrl' 					=> $countryDetails['serviceUrl']
			            ];
				}
			}
		}
		return $results;
	}

    /**
     * [getAccountDetails to get account complete details]
     * @param  boolean $amazon_AccountId [description]
     * @return [type]                    [description]
     */
    public function getAccountDetails($data = array()){
    		$sql = "SELECT * FROM ".DB_PREFIX."amazon_accounts WHERE 1 ";

        if(!empty($data['account_id'])){
            $sql .= "AND id = '".(int)$data['account_id']."' ";
        }

        if(!empty($data['report_id'])){
            $sql .= "AND wk_amazon_connector_listing_report_id = '".$this->db->escape($data['report_id'])."' ";
        }
        $result = $this->db->query($sql)->row;

    	return $result;
    }


    public function getAmazonServiceUrlAndMarketplaceId($accountCountryCode = false)
    {
        $countryCodeInfo = [
            'US'=>['serviceUrl'=>'https://mws.amazonservices.com', 'marketplaceId'=>'ATVPDKIKX0DER'],
            'UK'=>['serviceUrl'=>'https://mws-eu.amazonservices.com', 'marketplaceId'=>'A1F83G8C2ARO7P'],
            'DE'=>['serviceUrl'=>'https://mws-eu.amazonservices.com', 'marketplaceId'=>'A1PA6795UKMFR9'],
            'FR'=>['serviceUrl'=>'https://mws-eu.amazonservices.com', 'marketplaceId'=>'A13V1IB3VIYZZH'],
            'IT'=>['serviceUrl'=>'https://mws-eu.amazonservices.com', 'marketplaceId'=>'APJ6JRA9NG5V4'],
            'JP'=>['serviceUrl'=>'https://mws.amazonservices.jp', 'marketplaceId'=>'A1VC38T7YXB528'],
            'CN'=>['serviceUrl'=>'https://mws.amazonservices.com.cn', 'marketplaceId'=>'AAHKV2X7AFYLW'],
            'CA'=>['serviceUrl'=>'https://mws.amazonservices.com', 'marketplaceId'=>'A2EUQ1WTGCTBG2'],
            'MX'=>['serviceUrl'=>'https://mws.amazonservices.com', 'marketplaceId'=>'A1AM78C64UM0Y8'],
            'IN'=>['serviceUrl'=>'https://mws.amazonservices.in', 'marketplaceId'=>'A21TJRUUN4KGV'],
            'ES'=>['serviceUrl'=>'https://mws-eu.amazonservices.com', 'marketplaceId'=>'A1RKKUPIHCS9HS'],
            'GB'=>['serviceUrl'=>'https://mws.amazonservices.co.uk', 'marketplaceId'=>'A1F83G8C2ARO7P'],

        ];
        return isset($countryCodeInfo[$accountCountryCode]) ? $countryCodeInfo[$accountCountryCode] : false;
    }

    public function __getOcAmazonGlobalOption(){
			$result = $this->db->query("SELECT * FROM ".DB_PREFIX."option o LEFT JOIN ".DB_PREFIX."option_description od ON(o.option_id = od.option_id) WHERE od.name = 'Amazon Variations' ")->row;

			return $result;
		}

     /**
     * check product parent asin value
     * @param  array $variations
     * @return int|bool
     */
    public function checkParentAsinValue($variations)
    {
        $parentAsin = false;
        foreach ($variations as $value) {
            if (isset($value['MarketplaceASIN']['ASIN'])) {
                $parentAsin = $value['MarketplaceASIN']['ASIN'];
                break;
            }
        }
        return $parentAsin;
    }


    public function getMatchedProduct($productASIN, $accountId = false)
    {
        $this->loadFile('Products', 'GetMatchingProductSample');
        $config                 = $this->_getAccountConfigData($accountId);
        $config['productASIN']  = $productASIN;
        $matchingprodut = new \Getmatchingproduct((object)$config);
        $get_product = $matchingprodut->invokeGetMatchingProduct($matchingprodut->service, $matchingprodut->request);
        return $get_product;
    }

     public function GetMyPriceForASIN($productASIN, $accountId = false)
    {
        $this->loadFile('Products', 'GetMyPriceForASINSample');
        $config                 = $this->_getAccountConfigData($accountId);
        $config['productASIN']  = $productASIN;
        $matchingprodut         = new \GetMyPriceForASIN((object)$config);
        $get_product = $matchingprodut->invokeGetMyPriceForASIN($matchingprodut->service, $matchingprodut->request);
        return $get_product;
    }

    public function GetCompetitivePricingForASIN($productASIN, $accountId = false)
    {
        $this->loadFile('Products', 'GetCompetitivePricingForASINSample');
        $config                 = $this->_getAccountConfigData($accountId);
        $config['productASIN']  = $productASIN;
        $matchingproduct        = new \GetCompetitivePricingForASIN((object)$config);
        $get_product = $matchingproduct->invokeGetCompetitivePricingForASIN($matchingproduct->service, $matchingproduct->request);
        return $get_product;
    }

		public function __getProductFields($product_id = false){
				$result = $this->db->query("SELECT * FROM ".DB_PREFIX."amazon_product_fields WHERE product_id = '".(int)$product_id."' ")->row;
				return $result;
		}

		/**
		 * [_getEbayProductSpecification to get all the ebay specification]
		 * @return [type] [description]
		 */
		public function _getAmazonSpecification(){
			$amazonSpecifications = array();

			$getAmazonAttributeEntry = $this->db->query("SELECT DISTINCT aam.account_group_id, name, account_id FROM ".DB_PREFIX."amazon_attribute_map aam LEFT JOIN ".DB_PREFIX."attribute_group ag ON(aam.account_group_id = ag.attribute_group_id) LEFT JOIN ".DB_PREFIX."attribute_group_description agd ON(ag.attribute_group_id = agd.attribute_group_id) ")->rows;

			if(isset($getAmazonAttributeEntry) && $getAmazonAttributeEntry){
				foreach ($getAmazonAttributeEntry as $key => $specification) {
					$getConditionsValue = $this->db->query("SELECT *, ad.name as attribute_name FROM ".DB_PREFIX."attribute a LEFT JOIN ".DB_PREFIX."attribute_description ad ON (a.attribute_id = ad.attribute_id) LEFT JOIN ".DB_PREFIX."attribute_group ag ON(a.attribute_group_id = ag.attribute_group_id) LEFT JOIN ".DB_PREFIX."attribute_group_description agd ON(ag.attribute_group_id = agd.attribute_group_id) LEFT JOIN ".DB_PREFIX."amazon_attribute_map aam ON((a.attribute_group_id = aam.account_group_id) AND (a.attribute_id = aam.oc_attribute_id)) WHERE aam.account_group_id = '".(int)$specification['account_group_id']."' AND a.attribute_group_id = '".(int)$specification['account_group_id']."'  AND ad.language_id = '".(int)$this->config->get('config_language_id')."' ")->rows;
					$amazonSpecifications[$specification['account_group_id']]['name'] 			= $specification['name'];
					$amazonSpecifications[$specification['account_group_id']]['account_id'] = $specification['account_id'];
					$amazonSpecifications[$specification['account_group_id']]['attributes'] = $getConditionsValue;
				}
			}

			return $amazonSpecifications;
		}

		/**
		 * [getProductSpecification to get opencart product specification]
		 * @param  boolean $product_id [description]
		 * @return [type]              [description]
		 */
		public function getProductSpecification($product_id = false){
			$productSpecification = array();

			$getOcProductSpecification = $this->db->query("SELECT pa.attribute_id FROM " . DB_PREFIX . "product_attribute pa RIGHT JOIN ".DB_PREFIX."amazon_attribute_map aam ON(pa.attribute_id = aam.oc_attribute_id) WHERE pa.product_id = '" . (int)$product_id . "' GROUP BY pa.attribute_id")->rows;

			if(!empty($getOcProductSpecification)){
					foreach ($getOcProductSpecification as $product_attribute) {
						$product_attribute_description_data = array();

						$product_attribute_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

						foreach ($product_attribute_description_query->rows as $product_attribute_description) {
							$product_attribute_description_data[$product_attribute_description['language_id']] = array('text' => $product_attribute_description['text']);
						}

						$productSpecification[] = array(
							'attribute_id'                  => $product_attribute['attribute_id'],
							'product_attribute_description' => $product_attribute_description_data
						);
					}
			}
			return $productSpecification;
		}

		/**
		 * [checkSpecificationEntry to get only the Amazon specification [used to hide the specification for opencart attribute]]
		 * @param  array  $data [description]
		 * @return [type]       [description]
		 */
		public function checkSpecificationEntry($data = array()){
			$sql = "SELECT * FROM ".DB_PREFIX."attribute a LEFT JOIN ".DB_PREFIX."attribute_group ag ON(a.attribute_group_id = ag.attribute_group_id) WHERE a.attribute_group_id IN (SELECT account_group_id FROM ".DB_PREFIX."amazon_attribute_map) ";

			if(isset($data['attribute_id'])){
				$sql .= " AND a.attribute_id = '".(int)$data['attribute_id']."'";
			}

			if(isset($data['attribute_group_id'])){
				$sql .= " AND a.attribute_group_id = '".(int)$data['attribute_group_id']."' AND ag.attribute_group_id = '".(int)$data['attribute_group_id']."' ";
			}

			$getSpecificationEntry = $this->db->query($sql)->row;

			return $getSpecificationEntry;
		}

		public function _getAmazonVariation(){
			$results = array();
			$result = $this->db->query("SELECT * FROM ".DB_PREFIX."option o LEFT JOIN ".DB_PREFIX."option_description od ON(o.option_id = od.option_id) WHERE od.name = '".$this->db->escape('Amazon Variations')."' AND od.language_id = '".(int)$this->config->get('config_language_id')."' ")->row;

			if(isset($result['option_id']) && $result['option_id']){
				$query_option_value = $this->db->query("SELECT avm.*, ovd.*, ov.option_id FROM ".DB_PREFIX."amazon_variation_map avm LEFT JOIN ".DB_PREFIX."option_value ov ON(avm.variation_value_id = ov.option_value_id) LEFT JOIN ".DB_PREFIX."option_value_description ovd ON(ov.option_value_id = ovd.option_value_id) WHERE avm.variation_id = '".(int)$result['option_id']."' AND ov.option_id = '".(int)$result['option_id']."' AND ovd.language_id = '".(int)$this->config->get('config_language_id')."' ")->rows;

				$combination_values = array();
				if(!empty($query_option_value)){
					foreach ($query_option_value as $key => $combination_value) {
						$combination_values[] = $combination_value;
					}
				}

				$results = array(
					'option_id' 		=> $result['option_id'],
					'type' 					=>$result['type'],
					'option_name' 	=>$result['name'],
					'option_values' => $combination_values,
					);
			}
			return $results;
		}

		public function _getProductVariation($product_id = false, $type = 'amazon_product_variation'){
				$results = $product_option_value = array();

				$result = $this->db->query("SELECT po.*, od.* FROM ".DB_PREFIX."product_option po LEFT JOIN ".DB_PREFIX."product p ON(po.product_id = p.product_id) LEFT JOIN ".DB_PREFIX."option o ON(po.option_id = o.option_id) LEFT JOIN ".DB_PREFIX."option_description od ON(po.option_id = od.option_id) LEFT JOIN ".DB_PREFIX."amazon_variation_map avm ON (o.option_id = avm.variation_id) WHERE po.product_id = '".(int)$product_id."' AND od.name = '".$this->db->escape('Amazon Variations')."' ")->row;

				if(isset($result['option_id']) && $result['option_id']){
						$query = $this->db->query("SELECT pov.*, avm.*, apvm.* FROM ".DB_PREFIX."product_option_value pov LEFT JOIN ".DB_PREFIX."option_value_description ovd ON ((pov.option_value_id = ovd.option_value_id) AND (pov.option_id = ovd.option_id)) LEFT JOIN ".DB_PREFIX."amazon_variation_map avm ON ((pov.option_value_id = avm.variation_value_id) AND (pov.option_id = avm.variation_id)) LEFT JOIN ".DB_PREFIX."amazon_product_variation_map apvm ON((pov.product_option_value_id = apvm.product_option_value_id) AND (pov.option_value_id = apvm.option_value_id)) WHERE pov.option_id = '".(int)$result['option_id']."' AND pov.product_id = '".(int)$product_id."' AND pov.product_option_id = '".(int)$result['product_option_id']."' AND ovd.language_id = '".(int)$this->config->get('config_language_id')."' ")->rows;

					if(!empty($query)){
						foreach ($query as $key => $product_option_entry) {
							if($type == 'amazon_product_variation'){
								$results[] = $product_option_entry['option_value_id'];
							}

							if($type == 'amazon_product_variation_value'){
								$product_option_value[$product_option_entry['option_value_id']] = array(
												'name' 				=> $product_option_entry['value_name'],
												'quantity' 		=> $product_option_entry['quantity'],
												'price' 			=> $product_option_entry['price'],
												'price_prefix'=> $product_option_entry['price_prefix'],
												'id_type' 		=> $product_option_entry['id_type'],
												'id_value' 		=> $product_option_entry['id_value'],
												'sku' 				=> $product_option_entry['sku'],
											);
							}
						}
					}
					if($type == 'amazon_product_variation_value'){
							$results[$result['option_id']] = array(
											'option_id' 	=> $result['option_id'],
											'option_value' 	=> $product_option_value,
											);
							}
				}

				return $results;
			}

			/**
			 * [checkVariationEntry to check amazon variation entry]
			 * @param  array  $data [description]
			 * @return [type]       [description]
			 */
			public function checkVariationEntry($data = array()){
				$sql = "SELECT * FROM `" . DB_PREFIX . "product_option` po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN `" . DB_PREFIX . "option_description` od ON (o.option_id = od.option_id) WHERE po.option_id IN (SELECT variation_id FROM ".DB_PREFIX."amazon_variation_map) ";

				if(isset($data['product_id'])){
					$sql .= " AND po.product_id = '" . (int)$data['product_id'] . "' ";
				}

				if(isset($data['option_id'])){
					$sql .= " AND po.option_id = '" . (int)$data['option_id'] . "' ";
				}

				$getVariationEntry = $this->db->query($sql)->row;

				return $getVariationEntry;
			}

			/**
			 * [filterVariationASINEntry to check amazon product variation entry by product ASIN]
			 * @param  array  $data [description]
			 * @return [type]       [description]
			 */
			public function filterVariationASINEntry($data = array()){
				$variationASINEntry = array();

				$option = $this->__getOcAmazonGlobalOption();

				$sql = "SELECT pov.*, avm.*, apvm.*, ovd.*, apm.account_id FROM ".DB_PREFIX."product_option_value pov LEFT JOIN ".DB_PREFIX."option_value_description ovd ON ((pov.option_value_id = ovd.option_value_id) AND (pov.option_id = ovd.option_id)) LEFT JOIN ".DB_PREFIX."amazon_variation_map avm ON ((pov.option_value_id = avm.variation_value_id) AND (pov.option_id = avm.variation_id)) LEFT JOIN ".DB_PREFIX."amazon_product_variation_map apvm ON((pov.product_option_value_id = apvm.product_option_value_id) AND (pov.option_value_id = apvm.option_value_id)) LEFT JOIN ".DB_PREFIX."amazon_product_map apm ON (apvm.product_id = apm.oc_product_id) WHERE pov.option_id = '".(int)$option['option_id']."' AND ovd.language_id = '".(int)$this->config->get('config_language_id')."' ";

				if(isset($data['product_id'])){
					$sql .= " AND apvm.product_id = '" . (int)$data['product_id'] . "' ";
				}

				if(isset($data['account_id'])){
					$sql .= " AND apm.account_id = '" . (int)$data['account_id'] . "' ";
				}

				if(isset($data['product_option_value_id'])){
					$sql .= " AND apvm.product_option_value_id = '" . (int)$data['product_option_value_id'] . "' ";
				}

				if(isset($data['option_value_id'])){
					$sql .= " AND apvm.option_value_id = '" . (int)$data['option_value_id'] . "' ";
				}

				if(isset($data['id_value'])){
					$sql .= " AND apvm.id_value = '" . $data['id_value'] . "' ";
				}

				if(isset($data['main_product_type_value'])){
					$sql .= " AND apvm.main_product_type_value = '" . $data['main_product_type_value'] . "' ";
				}

				$variationASINEntry = $this->db->query($sql)->rows;

				return $variationASINEntry;
			}

			/*------------------------------ Submit feed Section -----------------------------------*/

	    /**
	     * submit feed
	     * @param  array $feedType
	     * @return array
	     */
	    public function submitFeed($feedType, $accountId)
	    {
	        $this->loadFile('', 'SubmitFeedSample');
					$config              = $this->_getAccountConfigData($accountId);
					$config['feedType']  = $feedType;
					$config['product']   = $this->product;
	        $submitFeed 				 = new \Submitfeed((object)$config);
	        $result = $submitFeed->invokeSubmitFeed($submitFeed->service, $submitFeed->request);
	        @fclose($submitFeed->feedHandle);
	        return $result;
	    }

			/**
	     * get feed submission list
	     * @param  int $submissionId
	     * @return object
	     */
	    public function getFeedSubmissionList($feedId, $accountId)
	    {
	        $this->loadFile('', 'GetFeedSubmissionListSample');
					$config              			= $this->_getAccountConfigData($accountId);
	        $config['submissionId']   = $feedId;
	        $feedsubmitlist 					= new \GetFeedSubmissionList((object)$config);
	        $feedsubmitresultDetail 	= $feedsubmitlist->invokeGetFeedSubmissionList($feedsubmitlist->service, $feedsubmitlist->request);

	        return $feedsubmitresultDetail;
	    }


			/**
	     * getFeedSubmissionResult
	     * @param  array $feedType
	     * @return array
	     */
	    public function getFeedSubmissionResult($feedId, $accountId)
	    {
				$this->loadFile('', 'GetFeedSubmissionResultSample');
				$config              = $this->_getAccountConfigData($accountId);
        $config['submissionId']   = $feedId;
        $feedsubmitresult    = new \GetFeedSubmissionResult((object)$config);
        $feedsubmitresultDetail = $feedsubmitresult->invokeGetFeedSubmissionResult($feedsubmitresult->service, $feedsubmitresult->request);

        return $feedsubmitresultDetail;
			}


			/*------------------------------ Import Order Section -----------------------------------*/

			/**
	     * getOrderList to get Orders List
	     * @param  array $data
	     * @return object
	     */
	    public function getOrderList($data, $accountId)
	    {
	        $this->loadFile('Orders', 'ListOrdersSample');
					$config              			= $this->_getAccountConfigData($accountId);
					$config['fromDate'] 			= $data['amazon_order_from'];
	        $config['toDate']   			= $data['amazon_order_to'];
	        $config['recordCount'] 		= $data['amazon_order_maximum'] ? $data['amazon_order_maximum'] : 10;
					$listOrders 							= new \Listorders((object)$config);
					$orders 									= $listOrders->invokeListOrders($listOrders->service, $listOrders->request);

					return $orders;
	    }

			/**
	     * [GetOrder to get detail of an order]
	     * @param [integer] $amazonOrderId [amazon order id]
	     */
	    public function GetOrder($amazonOrderId, $accountId)
	    {
	        $this->loadFile('Orders', 'GetOrderSample');
					$config              			= $this->_getAccountConfigData($accountId);
	        $config['amazonOrderId']  = $amazonOrderId;
	        $getOrder 								= new \Getorder((object)$config);
	        $amazon_Order = $getOrder->invokeGetOrder($getOrder->service, $getOrder->request);

					return $amazon_Order;
	    }

			/**
			 * [ListOrderItems to get ordered products of an order]
			 * @param [integer] $amazonOrderId [amazon order id]
			 */
			public function ListOrderItems($amazonOrderId, $accountId)
			{
					$this->loadFile('Orders', 'ListOrderItemsSample');
					$config              			= $this->_getAccountConfigData($accountId);
					$config['orderId']  			= $amazonOrderId;
					$Listorderitems 					= new \Listorderitems((object)$config);
					$ListorderitemsDetail 		= $Listorderitems->invokeListOrderItems($Listorderitems->service, $Listorderitems->request);

					return $ListorderitemsDetail;
			}

			/*------------------------------ Customer Section -----------------------------------*/

			/**
			 * [deleteCustomerEntry to delete amazon customer mapped entry]
			 * @param [integer] $customer_id [customer id]
			 */
			public function deleteCustomerEntry($customer_id)
			{
					$this->db->query("DELETE FROM ".DB_PREFIX."amazon_customer_map WHERE oc_customer_id = '".(int)$customer_id."' ");
			}
}
