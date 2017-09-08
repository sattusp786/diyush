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
 * @see MarketplaceWebServiceProducts_Model
 */

$path = getcwd().'/../Lib/amazon/amazonProducts/src/';
require_once($path.'MarketplaceWebServiceProducts/Model.php');


/**
 * MarketplaceWebServiceProducts_Model_CompetitivePriceList
 *
 * Properties:
 * <ul>
 *
 * <li>CompetitivePrice: array</li>
 *
 * </ul>
 */

class MarketplaceWebServiceProducts_Model_CompetitivePriceList extends MarketplaceWebServiceProducts_Model
{

    public function __construct($data = null)
    {
        $this->_fields =  [
        'CompetitivePrice' => ['FieldValue' => [], 'FieldType' => ['MarketplaceWebServiceProducts_Model_CompetitivePriceType']],
        ];
        parent::__construct($data);
    }

    /**
     * Get the value of the CompetitivePrice property.
     *
     * @return List<CompetitivePriceType> CompetitivePrice.
     */
    public function getCompetitivePrice()
    {
        if ($this->_fields['CompetitivePrice']['FieldValue'] == null) {
            $this->_fields['CompetitivePrice']['FieldValue'] = [];
        }
        return $this->_fields['CompetitivePrice']['FieldValue'];
    }

    /**
     * Set the value of the CompetitivePrice property.
     *
     * @param array competitivePrice
     * @return this instance
     */
    public function setCompetitivePrice($value)
    {
        if (!$this->_isNumericArray($value)) {
            $value =  [$value];
        }
        $this->_fields['CompetitivePrice']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Clear CompetitivePrice.
     */
    public function unsetCompetitivePrice()
    {
        $this->_fields['CompetitivePrice']['FieldValue'] = [];
    }

    /**
     * Check to see if CompetitivePrice is set.
     *
     * @return true if CompetitivePrice is set.
     */
    public function isSetCompetitivePrice()
    {
                return !empty($this->_fields['CompetitivePrice']['FieldValue']);
    }

    /**
     * Add values for CompetitivePrice, return this.
     *
     * @param competitivePrice
     *             New values to add.
     *
     * @return This instance.
     */
    public function withCompetitivePrice()
    {
        foreach (func_get_args() as $CompetitivePrice) {
            $this->_fields['CompetitivePrice']['FieldValue'][] = $CompetitivePrice;
        }
        return $this;
    }
}
