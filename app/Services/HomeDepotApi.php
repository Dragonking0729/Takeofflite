<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class HomeDepotApi
{

    private $storeId = "2902";
    private $zipCode = "39759";

    public function getStoreId()
    {
        return $this->storeId;
    }

    public function getZipCode()
    {
        return $this->zipCode;
    }

    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
    }

    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;
    }

    public function setLocationFromZipCode($zipCode)
    {
        $this->setZipCode($zipCode);
        $stores = $this->searchStoresByZipcode($zipCode);
        $storeId = $stores[0]['storeId'];
        $this->setStoreId($storeId);
    }

    public function setLocationFromId($storeId)
    {
        $this->setStoreId($storeId);
        $stores = $this->searchStoresById($storeId);
        $zipCode = $stores[0]['address']['postalCode'];
        $this->setZipCode($zipCode);
    }

    public function getHeaders()
    {
        $headers = [
            'Accept: application/json',
            'X-APOLLO-OPERATION-NAME: ProductModel',
            'x-customer-type: B2C',
            'User-Agent: THDConsumer/6.17.2 (Linux; Android 5.1.1; SM-J320H Build/LMY47V) Mobile;neoload',
            'x-nativeapp: f28582ec53c5',
            'X-APOLLO-CACHE-DO-NOT-STORE: true',
            'X-Experience-Name: native-apps-android',
            'Content-Type: application/json; charset=utf-8',
            'Accept-Encoding: identity',
        ];
        return $headers;
    }

    public function fetchProduct($itemId = null)
    {
        $url = 'https://www.homedepot.com/federation-gateway/graphql';
        $headers = $this->getHeaders();
        $data = '{"operationName":"ProductModel","variables":{"itemId":"' . $itemId . '","storeId":"' . $this->storeId . '","zipCode":"11235","nearbyStores":[],"storefilter":"ALL"},"query":"query ProductModel($itemId: String!, $storeId: String!, $zipCode: String!, $nearbyStores: [String!], $membership:LoyaltyMembershipInput) { product(itemId: $itemId, loyaltyMembershipInput: $membership) { __typename identifiers { __typename ...PrimaryIdentifiers } info { __typename ...PrimaryInfo } reviews { __typename ...ReviewsInfo } pricing { __typename ...PricingDetails } fulfillment(storeId: $storeId, zipCode: $zipCode, nearbyStores: $nearbyStores) { __typename ...FulfillmentDetails } media { __typename ...MediaInfo } availabilityType { __typename ...AvailabilityInfo } specificationGroup { __typename ...SpecificationsGroup } taxonomy { __typename ...TaxonomyGroup } badges { __typename ...BadgesGroup } details { __typename ...ProductDetails } ...SizeAndFitDetailGroup ...KeyProductFeaturesGroup installServices { __typename ...InstallServicesGroup } ...SubscriptionGroup } } fragment SizeAndFitDetailGroup on Product { __typename sizeAndFitDetail { attributeGroups { attributes { attributeName dimensions __typename } dimensionLabel productType __typename } __typename } } fragment KeyProductFeaturesGroup on Product { __typename keyProductFeatures { keyProductFeaturesItems { features { name refinementId refinementUrl value __typename } __typename } __typename } } fragment SubscriptionGroup on Product { __typename subscription { defaultfrequency discountPercentage subscriptionEnabled __typename } } fragment PrimaryIdentifiers on Identifiers { itemId modelNumber upc vendorNumber storeSkuNumber productLabel canonicalUrl omsThdSku brandName isSuperSku parentId upcGtin13 specialOrderSku toolRentalSkuNumber sampleId productType __typename } fragment PrimaryInfo on Info { __typename applianceBundled bareCushion calculatorType classNumber colorNumber colorWallEligible displayWhatWeOffer dotComColorEligible ecoRebate fbrItems fbtItems fiscalYear genericBrand genericName globalCustomConfigurator { __typename customExperience } hasServiceAddOns hasSubscription hasVisuallySimilar hazardCode hidePrice inStoreAssemblyAvailable irgItems isBuryProduct isGenericProduct isLiveGoodsProduct isSponsored isSureFit isSpecialBuy minimumOrderQuantity paintBrand productDepartment projectCalculatorEligible prop65Warning protectionPlanSku quantityLimit recommendationFlags { __typename ACC badges collections featureBasedRecommendations frequentlyBoughtTogether promotionalBundle visualNavigation } replacementOMSID returnable sizeChart sponsoredBeacon { __typename onClickBeacon onViewBeacon } sponsoredMetadata { __typename slotId campaignId placementId } sskMax sskMin swatches { __typename swatchImgUrl value label itemId isSelected url } subClassNumber totalNumberOfOptions unitOfMeasureCoverage wasMinPriceRange wasMaxPriceRange productSubType { __typename name link } productDepartmentId categoryHierarchy } fragment ReviewsInfo on Reviews { __typename ratingsReviews { __typename averageRating totalReviews } } fragment PricingDetails on Pricing { __typename alternate { __typename bulk { __typename pricePerUnit thresholdQuantity value } unit { __typename caseUnitOfMeasure unitsOriginalPrice unitsPerCase value } } alternatePriceDisplay mapAboveOriginalPrice message original promotion { __typename dates { __typename end start } description { __typename shortDesc longDesc } reward { __typename tiers { __typename tier minPurchaseQuantity maxPurchaseQuantity rewardPercent rewardAmountPerOrder rewardAmountPerItem rewardFixedPrice minPurchaseAmount } } experienceTag subExperienceTag itemList dollarOff isCartLevelPromotion percentageOff promoCode savingsCenter savingsCenterPromos specialBuyDollarOff specialBuyPercentageOff specialBuySavings type } specialBuy unitOfMeasure value preferredPriceFlag } fragment FulfillmentDetails on Fulfillment { __typename backordered backorderedShipDate excludedShipStates bossExcludedShipStates seasonStatusEligible bodfsAssemblyEligible inStoreAssemblyEligible onlineStoreStatusType anchorStoreStatusType onlineStoreStatus anchorStoreStatus fulfillmentOptions { __typename type fulfillable services { __typename type mode { __typename code desc longDesc group } isDefault deliveryCharge freeDeliveryThreshold itemLevelFreeShipping hasFreeShipping dynamicEta { __typename hours minutes } deliveryTimeline deliveryDates { __typename startDate endDate } locations { __typename type isAnchor locationId storeName storePhone state distance curbsidePickupFlag isBuyInStoreCheckNearBy inventory { __typename quantity isInStock isOutOfStock isLimitedQuantity isUnavailable } } } messages } } fragment MediaInfo on Media { __typename images { __typename url type subType sizes } video { __typename url videoStill thumbnail longDescription shortDescription videoId type link { __typename text url } title } threeSixty { __typename id url } augmentedRealityLink { __typename usdz image } } fragment AvailabilityInfo on AvailabilityType { __typename discontinued buyable status type } fragment SpecificationsGroup on SpecificationGroup { __typename specTitle specifications { __typename specName specValue itemProp } } fragment TaxonomyGroup on Taxonomy { __typename brandLinkUrl breadCrumbs { __typename dimensionName label url browseUrl creativeIconUrl dimensionId refinementKey deselectUrl } } fragment BadgesGroup on Badge { __typename color creativeImageUrl endDate label message name timerDuration } fragment ProductDetails on Details { __typename description descriptiveAttributes { __typename name value bulleted sequence } highlights infoAndGuides { __typename name url } installation { __typename howToBuy salientPoints serviceType leadGenUrl programCategory contactNumber } collection { __typename collectionId name url type } } fragment InstallServicesGroup on InstallServices { scheduleAMeasure __typename }"}';
        $contents = $this->send('POST', $url, $data, $headers, [
            CURLOPT_TIMEOUT => 10,
        ]);
        $json = json_decode($contents, true);
        $product = null;
        if (isset($json['data']['product']))
            $product = $json['data']['product'];
        return $product;
    }

    public function fetchProductPrice($itemId = null)
    {
        $product = $this->fetchProduct($itemId);
        $value = null;
        if (isset($product['pricing']['value']))
            $value = $product['pricing']['value'];
        return $value;
    }

    public function fetchProductIdentifiers($itemId = null)
    {
        $product = $this->fetchProduct($itemId);
        $value = null;
        if (isset($product['identifiers']))
            $value = $product['identifiers'];
        return $value;
    }

    public function searchStoresById($storeId = null)
    {
        $url = 'https://nativeapp.homedepot.com/StoreSearchServices/v2/storesearch?storeType=retail&storeid=' . $storeId . '&type=json&pagesize=20&countryRegion=US&key=8GdxXVBsFAzhkvLfn78NLnzQkDZme0KW';
        $headers = $this->getHeaders();
        $contents = $this->send('GET', $url, null, $headers);
        $json = json_decode($contents, true);
        $data = $json['stores'];
        return $data;
    }

    public function searchStoresByZipcode($zipcode = null)
    {
        $url = 'https://nativeapp.homedepot.com/StoreSearchServices/v2/storesearch?storeType=retail&radius=100&zipcode=' . $zipcode . '&type=json&pagesize=20&countryRegion=US&key=8GdxXVBsFAzhkvLfn78NLnzQkDZme0KW';
        $headers = $this->getHeaders();
        $contents = $this->send('GET', $url, null, $headers);
        $json = json_decode($contents, true);
        $data = $json['stores'];
        return $data;
    }

    public function send($method, $url, $data = array(), $headers = array(), $options = [])
    {
        $default_options = array(
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36',
            CURLOPT_REFERER => 'http://google.com',
            CURLOPT_VERBOSE => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => 0,
            CURLOPT_POST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CONNECTTIMEOUT => 100,
            CURLOPT_TIMEOUT => 10,
        );
        $options = array_replace($default_options, $options);
        $options = array_replace($options, array(CURLOPT_URL => $url));
        if ($method == 'GET')
            $options = array_replace($options, array(CURLOPT_CUSTOMREQUEST => 'GET'));
        if ($method == 'POST')
            $options = array_replace($options, array(CURLOPT_POST => true));
        if (!empty($data))
            $options = array_replace($options, array(CURLOPT_POSTFIELDS => $data));
        if (!empty($headers))
            $options = array_replace($options, array(CURLOPT_HTTPHEADER => $headers));
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        return $response;
    }

}