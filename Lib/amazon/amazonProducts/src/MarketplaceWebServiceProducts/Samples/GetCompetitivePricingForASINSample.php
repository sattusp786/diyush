<?php 

class GetCompetitivePricingForASIN
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
        * sample for Get Matching Product Action
        ***********************************************************************/
        // @TODO: set request. Action can be passed as MarketplaceWebServiceProducts_Model_GetMatchingProduct
        $this->request = new MarketplaceWebServiceProducts_Model_GetCompetitivePricingForASINRequest();
        $this->request->setSellerId($amazonmws->sellerId);
        //$this->request->setMWSAuthToken($amazonmws->mwsAuthToken); // Optional
        $this->request->setMarketplaceId($amazonmws->marketplaceId);
        $this->request->setASINList(new MarketplaceWebServiceProducts_Model_ASINListType(["ASIN" => [$amazonmws->productASIN]]));
        //$this->request->setASINList(new MarketplaceWebServiceProducts_Model_ASINListType(array("ASIN" => $amazonmws->productASIN)));
    }

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
    //$service = new MwsProductsMock();

    /************************************************************************
     * Setup request parameters and uncomment invoke to try out
     * sample for Get Competitive Pricing For ASIN Action
     ***********************************************************************/

    /**
     * Get Get Competitive Pricing For ASIN Action Sample
     * Gets competitive pricing and related information for a product identified by
     * the MarketplaceId and ASIN.
     *
     * @param MwsProductsInterface $service instance of MwsProductsInterface
     * @param mixed                                   $request GetCompetitivePricingForASIN or array of parameters
     */

    function invokeGetCompetitivePricingForASIN(MarketplaceWebServiceProducts_Interface $service, $request)
    {
        try
        {
            $response = $service->GetCompetitivePricingForASIN($request);

            $dom = new DOMDocument();
            $dom->loadXML($response->toXML());
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $xmlfile = $dom->saveXML();

            $xmlfile = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $xmlfile);
            $xmlfile = simplexml_load_string($xmlfile, "SimpleXMLElement", LIBXML_NOCDATA);
            $jsonxml = json_encode($xmlfile);
            $matchedproduct = json_decode($jsonxml, true);

            return $matchedproduct;
        }

        catch (MarketplaceWebServiceProducts_Exception $ex)
        {
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