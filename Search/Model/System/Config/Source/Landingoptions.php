<?php

namespace Klevu\Search\Model\System\Config\Source;

class Landingoptions {

    const YES    = 1;
    const NO     = 0;
    const KlEVULAND = 2;
	
	public function toOptionArray() {
		$check_preserve = \Magento\Framework\App\ObjectManager::getInstance()->get('Klevu\Search\Model\Product\Sync')->getFeatures();
        if(!empty($check_preserve['disabled'])) {
            if(strpos($check_preserve['disabled'],"preserves_layout") !== false) {
				return [
				   ['value' => static::KlEVULAND, 'label' => __('Based on Klevu Template (Recommended)')],
				   ['value' => static::NO, 'label' => __('Disable')],
				];
            } else {
				return [
				   ['value' => static::YES, 'label' => __('Preserves Your Theme Layout**')],
				   ['value' => static::KlEVULAND, 'label' => __('Based on Klevu Template (Recommended)')],
				   ['value' => static::NO, 'label' => __('Disable')],
				];
            }
        } else if(empty($check_preserve['disabled'])){
                return [
				   ['value' => static::NO, 'label' => __('Disable')],
				   ['value' => static::KlEVULAND, 'label' => __('Based on Klevu Template (Recommended)')],
				   ['value' => static::YES, 'label' => __('Preserves Your Theme Layout**')],

				];
        } else {
                return [
				   ['value' => static::NO, 'label' => __('Disable')],
				   ['value' => static::KlEVULAND, 'label' => __('Based on Klevu Template (Recommended)')],
				];
        }

    }
}
