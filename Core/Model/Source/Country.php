<?php
namespace Payments\Core\Model\Source;

/**
 * Class Country
 */
class Country extends \Magento\Directory\Model\Config\Source\Country
{
    /**
     * Return options array
     *
     * @param boolean $isMultiselect
     * @param string|array $foregroundCountries
     * @return array
     */
    public function toOptionArray($isMultiselect = false, $foregroundCountries = '')
    {
        if (!$this->_options) {
            $this->_options = $this->_countryCollection->loadData()->setForegroundCountries(
                $foregroundCountries
            )->toOptionArray(
                false
            );
        }

        $options = $this->_options;
        foreach ($options as $i => $option) {
            if (in_array($option['value'], ['US', 'CA'])) {
                unset($options[$i]);
                array_unshift($options, $option);
            }
        }
        if (!$isMultiselect) {
            array_unshift($options, ['value' => '', 'label' => __('--Please Select--')]);
        }

        return $options;
    }
}
