<?php

class Edge_EmailCrossell_Block_Order_Email_Items_Crossell extends Mage_Catalog_Block_Product_Abstract
{
    /**
     * Items quantity will be capped to this value
     *
     * @var int
     */
    protected $_maxItemCount = 4;

    /**
     * Get crosssell items
     *
     * @return array
     */
    public function getItems()
    {
        $items = $this->getData('items');
        if (is_null($items)) {
            $items = array();

            $ninProductIds = $this->_getOrderProductIds();
            
            if ($ninProductIds) {
                if (count($items) < $this->_maxItemCount) {
                    $filterProductIds = array_merge($ninProductIds, $this->_getOrderProductIdsRel());
                    $collection = $this->_getCollection()
                        ->addProductFilter($filterProductIds)
                        ->addExcludeProductFilter($ninProductIds)
                        ->setPageSize($this->_maxItemCount-count($items))
                        ->setGroupBy()
                        ->setPositionOrder()
                        ->load();
                    foreach ($collection as $item) {
                        $items[] = $item;
                    }
                }
                
            }
            
            $this->setData('items', $items);
        }
        return $items;
    }

    /**
     * Count items
     *
     * @return int
     */
    public function getItemCount()
    {
        return count($this->getItems());
    }

    /**
     * Get product ids for Order
     *
     * @return array
     */
    protected function _getOrderProductIds()
    {
        $ids = $this->getData('_cart_product_ids');
        if (is_null($ids)) {
            $ids = array();
            
            foreach ($this->getOrder()->getItemsCollection() as $item) {
                $ids[] = $item->getProductId();
            }
            
            $this->setData('_cart_product_ids', $ids);
        }
        return $ids;
    }

    /**
     * Retrieve Array of product ids which have special relation with products in Order
     * For example simple product as part of Grouped product
     *
     * @return array
     */
    protected function _getOrderProductIdsRel()
    {
        $productIds = array();
        
        foreach ($this->getOrder()->getItemsCollection() as $orderItem) {
            if ($orderItem->getProductType() == Mage_Catalog_Model_Product_Type_Grouped::TYPE_CODE) {
                $productIds[] = $orderItem->getProductOptions()['info_buyRequest']['super_product_config']['product_id'];
            }
        }
        
        return $productIds;
    }

    /**
     * Get crosssell products collection
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Link_Product_Collection
     */
    protected function _getCollection()
    {
        $collection = Mage::getModel('catalog/product_link')->useCrossSellLinks()
            ->getProductCollection()
            ->setStoreId(Mage::app()->getStore()->getId())
            ->addStoreFilter()
            ->setPageSize($this->_maxItemCount);
        $this->_addProductAttributesAndPrices($collection);

        Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
        Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);

        return $collection;
    }
}