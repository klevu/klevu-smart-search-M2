<?php
namespace Klevu\Search\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\ImportExport\Model\Export\Adapter\Csv;
use \Magento\Catalog\Model\ResourceModel\Category\Collection;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\Data\Tree\Node;

class FeedCron extends \Magento\Framework\DataObject 
{
    protected $productRepository;
    protected $searchCriteriaBuilder;
    protected $filterBuilder;
    protected $csv;
    protected $resourceCategoryCollection;
    protected $storeModelStoreManagerInterface;
    protected $category_paths = null;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        Csv $csv,
        \Magento\Catalog\Model\ResourceModel\Category\Collection $resourceCategoryCollection,
        \Magento\Store\Model\StoreManagerInterface $storeModelStoreManagerInterface
    ) {
        $this->resourceCategoryCollection = $resourceCategoryCollection;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->storeModelStoreManagerInterface = $storeModelStoreManagerInterface;
        $this->csv = $csv;
    }


    public function export()
    {
        $date = $this->getLastWeekDate();
        $items = $this->getProducts($date);    
        $this->writeToFile($items);
    }

    protected function getLastWeekDate()
    {
        $now = new \DateTime();
        $interval = new \DateInterval('P1W');
        $lastWeek = $now->sub($interval);
        return $lastWeek;
    }

    public function getProducts($date)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('created_at','2015-11-17 17:17:31','gt')->create();
        $products = $this->productRepository->getList($searchCriteria);
        return $products->getItems();

    }
    
        /**
     * Return an array of category paths for all the categories in the
     * current store, not including the store root.
     *
     * @return array A list of category paths where each key is a category
     *               ID and each value is an array of category names for
     *               each category in the path, the last element being the
     *               name of the category referenced by the ID.
     */
    protected function getCategoryPaths() {
        if (!$category_paths = $this->getData('category_paths')) {
            $category_paths = array();
            $rootId = $this->storeModelStoreManagerInterface->getStore()->getRootCategoryId();  
            $collection = $this->resourceCategoryCollection
                ->setStoreId($this->storeModelStoreManagerInterface->getStore()->getId())
                ->addFieldToFilter('level', array('gt' => 1))
                ->addFieldToFilter('path', array('like'=> "1/$rootId/%"))
                ->addIsActiveFilter()
                ->addNameToResult();

            foreach ($collection as $category) {
                    $category_paths[$category->getId()] = array();
                    $path_ids = $category->getPathIds();
                    foreach ($path_ids as $id) {
                        if ($item = $collection->getItemById($id)) {
                            $category_paths[$category->getId()][] = $item->getName();
                        }
                    }
            }

            $this->setData('category_paths', $category_paths);
        }

        return $category_paths;
    }

    /**
     * Return a list of the names of all the categories in the
     * paths of the given categories (including the given categories)
     * up to, but not including the store root.
     *
     * @param array $categories
     *
     * @return array
     */
    protected function getCategoryNames(array $categories) {
        $category_paths = $this->getCategoryPaths();

        $result = array("KLEVU_PRODUCT");
        foreach ($categories as $category) {
            if (isset($category_paths[$category])) {
                $result = array_merge($result, $category_paths[$category]);
            }
        }

        return array_unique($result);
    }
    
    
    /**
     * Given a list of category IDs, return the name of the category
     * in that list that has the longest path.
     *
     * @param array $categories
     *
     * @return string
     */
    protected function getLongestPathCategoryName(array $categories) {
        $category_paths = $this->getCategoryPaths();

        $length = 0;
        $name = "";
        foreach ($categories as $id) {
            if (isset($category_paths[$id])) {
                if (count($category_paths[$id]) > $length) {
                    $length = count($category_paths[$id]);
                    $name = end($category_paths[$id]);
                }
            }
        }

        return $name;
    }
    
        /**
     * Returns either array containing the label and value(s) of an attribute, or just the given value
     *
     * In the case that there are multiple options selected, all values are returned
     *
     * @param string $code
     * @param null   $value
     *
     * @return array|string
     */
    protected function getAttributeData($code, $value = null) {
        if (!$attribute_data = $this->getData('attribute_data')) {
            $attribute_data = array();

            $collection = $this->_productAttributeCollection
                ->addFieldToFilter('attribute_code', array('in' => $this->getUsedMagentoAttributes()));

            foreach ($collection as $attr) {
                $attr->setStoreId($this->getStore()->getId());
                $attribute_data[$attr->getAttributeCode()] = array(
                    'label' => $attr->getStoreLabel($this->getStore()->getId()),
                    'values' => ''
                );

                if ($attr->usesSource()) {
//                    $attribute_data[$attr->getAttributeCode()] = array();
                    foreach($attr->setStoreId($this->getStore()->getId())->getSource()->getAllOptions(false) as $option) {
                        if (is_array($option['value'])) {
                            foreach ($option['value'] as $sub_option) {
                                if(count($sub_option) > 0) {
                                    $attribute_data[$attr->getAttributeCode()]['values'][$sub_option['value']] = $sub_option['label'];
                                }
                            }
                        } else {
                            $attribute_data[$attr->getAttributeCode()]['values'][$option['value']] = $option['label'];
                        }
                    }
                }
            }

            $this->setData('attribute_data', $attribute_data);
        }
        // make sure the attribute exists
        if (isset($attribute_data[$code])) {
            // was $value passed a parameter?
            if (!is_null($value)) {
                // If not values are set on attribute_data for the attribute, return just the value passed. (attributes like: name, description etc)
                if(empty($attribute_data[$code]['values'])) {
                    return $value;
                }
                // break up our value into an array by a comma, this is for catching multiple select attributes.
                $values = explode(",", $value);

                // loop over our array of attribute values
                foreach ($values as $key => $valueOption) {
                    // if there is a value on the attribute_data use that value (it will be the label for a dropdown select attribute)
                    if (isset($attribute_data[$code]['values'][$valueOption])) {
                        $values[$key] = $attribute_data[$code]['values'][$valueOption];
                    } else { // If no label was found, log an error and unset the value.
                        $this->_searchHelperData->log(\Zend\Log\Logger::WARN, sprintf("Attribute: %s option label was not found, option ID provided: %s", $code, $valueOption));
                        unset($values[$key]);
                    }
                }

                // If there was only one value in the array, return the first (select menu, single option), or if there was more, return them all (multi-select).
                if (count($values) == 1) {
                    $attribute_data[$code]['values'] = $values[0];
                } else {
                    $attribute_data[$code]['values'] =  $values;
                }

            }
            return $attribute_data[$code];
        }

        $result['label'] = $code;
        $result['values'] = $value;
        return $result;
    }

    protected function writeToFile($items)
    {
        if (count($items) > 0) {
            $this->csv->setHeaderCols(['id','type_id','created_at','sku','price','name','image','desc','shortDesc','url','category','listCategory']);
            foreach ($items as $item) {
                $this->csv->writeRow(['id'=>$item->getId(),'type_id'=>$item->getTypeId(), 'created_at' => $item->getCreatedAt(), 'sku' => $item->getSku(),'price' => $item->getPrice() ,'name'=> $item->getName(),'image' =>$item->getImage(),'desc'=>$item->getDescription(),'shortDesc' => $item->getDescription(),'url' => $item->getUrl(),
                'category' => $this->getLongestPathCategoryName($item->getCategoryIds()),'listCategory'=> implode(";",$this->getCategoryNames($item->getCategoryIds()))]);
            }    
        }
    }
}
