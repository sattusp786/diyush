<?php
/*******************************************************************************
 * Copyright 2009-2015 Amazon Services. All Rights Reserved.
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 *
 * You may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at: http://aws.amazon.com/apache2.0
 * This file is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR 
 * CONDITIONS OF ANY KIND, either express or implied. See the License for the 
 * specific language governing permissions and limitations under the License.
 *******************************************************************************
 * PHP Version 5
 * @category Amazon
 * @package  Marketplace Web Service Products
 * @version  2011-10-01
 * Library Version: 2015-09-01
 * Generated: Thu Sep 10 06:52:22 PDT 2015
 */

/**
 * Get Matching Product For Id Sample
 */

class GetMatchingProductForId
{
  
    public $request;
    public $service;

    public function __construct($amazonmws)
    {
        require_once('.config.inc.php');

        /************************************************************************
         * Instantiate Implementation of MarketplaceWebServiceProducts
         *
         * AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY constants
         * are defined in the .config.inc.php located in the same
         * directory as this sample
         ***********************************************************************/
        // More endpoints are listed in the MWS Developer Guide
        // North America:
        //$serviceUrl = "https://mws.amazonservices.com/Products/2011-10-01";
        // Europe
        //$serviceUrl = "https://mws-eu.amazonservices.com/Products/2011-10-01";
        // Japan
        //$serviceUrl = "https://mws.amazonservices.jp/Products/2011-10-01";
        // China
        //$serviceUrl = "https://mws.amazonservices.com.cn/Products/2011-10-01";

         $serviceUrl = $amazonmws->serviceUrl."/Products/2011-10-01";

         $config =  [
         'ServiceURL' => $serviceUrl,
         'ProxyHost' => null,
         'ProxyPort' => -1,
         'ProxyUsername' => null,
         'ProxyPassword' => null,
         'MaxErrorRetry' => 3,
         ];

         $this->service = new MarketplaceWebServiceProducts_Client(
             $amazonmws->accessKeyId,
             $amazonmws->secretKey,
             $amazonmws->applicationName,
             $amazonmws->applicationVersion,
             $config
         );

        /************************************************************************
         * Uncomment to try out Mock Service that simulates MarketplaceWebServiceProducts
         * responses without calling MarketplaceWebServiceProducts service.
         *
         * Responses are loaded from local XML files. You can tweak XML files to
         * experiment with various outputs during development
         *
         * XML files available under MarketplaceWebServiceProducts/Mock tree
         *
         ***********************************************************************/
         // $service = new MarketplaceWebServiceProducts_Mock();

        /************************************************************************
         * Setup request parameters and uncomment invoke to try out
         * sample for Get Matching Product For Id Action
         ***********************************************************************/
         // @TODO: set request. Action can be passed as MarketplaceWebServiceProducts_Model_GetMatchingProductForId
     
     
         $this->request = new MarketplaceWebServiceProducts_Model_GetMatchingProductForIdRequest();
         $this->request->setSellerId($amazonmws->sellerId);
         $this->request->setMarketplaceId($amazonmws->marketplaceId);
         $this->request->setIdType($amazonmws->idType);
         $this->request->setIdList(new MarketplaceWebServiceProducts_Model_IdListType(["Id" => $amazonmws->idList]));

         // object or array of parameters
         //invokeGetMatchingProductForId($service, $request);
    }

  /**
   * Get Get Matching Product For Id Action Sample
   * Gets competitive pricing and related information for a product identified by
   * the MarketplaceId and ASIN.
   *
   * @param MarketplaceWebServiceProducts_Interface $service instance of MarketplaceWebServiceProducts_Interface
   * @param mixed $request MarketplaceWebServiceProducts_Model_GetMatchingProductForId or array of parameters
   */

    function invokeGetMatchingProductForId(MarketplaceWebServiceProducts_Interface $service, $request)
    {
        try {
            $response = $service->GetMatchingProductForId($request);

            $dom = new DOMDocument();
            $dom->loadXML($response->toXML());
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $xmlfile = $dom->saveXML();

          //$xml = simplexml_load_string($xmlfile);
          //$jsonxml = json_encode($xml);
          //$matchingprod = json_decode($jsonxml,TRUE);

            return $xmlfile;
        } catch (MarketplaceWebServiceProducts_Exception $ex) {
            echo("Caught Exception: " . $ex->getMessage() . "\n");
            echo("Response Status Code: " . $ex->getStatusCode() . "\n");
            echo("Error Code: " . $ex->getErrorCode() . "\n");
            echo("Error Type: " . $ex->getErrorType() . "\n");
            echo("Request ID: " . $ex->getRequestId() . "\n");
            echo("XML: " . $ex->getXML() . "\n");
            echo("ResponseHeaderMetadata: " . $ex->getResponseHeaderMetadata() . "\n");
        }
    }
}
