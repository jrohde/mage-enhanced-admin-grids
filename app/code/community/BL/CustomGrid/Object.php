<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   BL
 * @package    BL_CustomGrid
 * @copyright  Copyright (c) 2015 Benoît Leulliette <benoit.leulliette@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class BL_CustomGrid_Object extends Varien_Object
{
    /**
     * Keys of the objects that should be cloned when the containing object is
     * 
     * @var array
     */
    protected $_clonableObjectsKeys = array();
    
    /**
     * Clone the necessary contained objects
     * 
     * @param array $data Main data array, or a subset
     * @param string $key Key of the given data array
     */
    protected function _cloneObjects(array &$data, $key = '')
    {
        foreach ($data as $subKey => $value) {
            $valueKey = ($key !== '' ? $key . '/' . $subKey : $subKey);
            
            if (is_array($value)) {
                $this->_cloneObjects($data[$subKey], $valueKey);
            } elseif (in_array($valueKey, $this->_clonableObjectsKeys, true)) {
                $data[$subKey] = clone $value;
            }
        }
    }
    
    public function __clone()
    {
        $this->_cloneObjects($this->_data);
    }
    
    /**
     * Return the keys of the objects that should be cloned when the containing object is
     * 
     * @return array
     */
    public function getClonableObjectsKeys()
    {
        return $this->_clonableObjectsKeys;
    }
    
    /**
     * Set the keys of the objects that should be cloned when the containing object is
     * 
     * @param array $keys Data keys
     * @return BL_CustomGrid_Object
     */
    public function setClonableObjectsKeys(array $keys)
    {
        $this->_clonableObjectsKeys = array_unique($keys);
        return $this;
    }
    
    /**
     * Add keys of objects that should be cloned when the containing object is
     * 
     * @param array $keys Data kets
     * @return BL_CustomGrid_Object
     */
    public function addClonableObjectsKeys(array $keys)
    {
        $this->_clonableObjectsKeys = array_unique(array_merge($this->_clonableObjectsKeys, $keys));
        return $this;
    }
    
    /**
     * Complement the original data with the given data
     *
     * @param array $data Data to only add if missing (on a per-value basis)
     * @return BL_CustomGrid_Object
     */
    public function complementData(array $data)
    {
        foreach ($data as $key => $value) {
            if (!$this->hasData($key)) {
                $this->setData($key, $value);
            }
        }
        return $this;
    }
    
    /**
     * Merge the given data into the original data
     * 
     * @param array $data Data to merge
     * @param bool $recursive Whether the merging should be recursive
     * @return BL_CustomGrid_Object
     */
    public function mergeData(array $data, $recursive = true)
    {
        if ($recursive) {
            $this->_data = array_merge_recursive($this->_data, $data);
        } else {
            $this->_data = array_merge($this->_data, $data);
        }
        return $this;
    }
    
    /**
     * Append the given value or array to the given data key
     * If the corresponding data value is not an array, then it will be wrapped into an array before the appending
     * Note that if the appended value is an array, its keys will be lost
     * 
     * @param string $key Data key
     * @param mixed $value Value or array to append
     * @return BL_CustomGrid_Object
     */
    public function appendData($key, $value)
    {
        if (!is_array($data = $this->getDataSetDefault($key, array()))) {
            $data = array($data);
        }
        if (is_array($value)) {
            $data = array_merge($data, array_values($value));
        } else {
            $data[] = $value;
        }
        $this->setData($key, $data);
        return $this;
    }
    
    /**
     * Substract the given value from the given data key
     * If the corresponding data value is an array, all the sub values that it contains which are (strictly) equal
     * to the given value will be unset
     * Otherwise, the data key will be unset if the data value is (strictly) equal to the given value
     * 
     * @param string $key Data key
     * @param mixed $value Substracted value
     * @param bool $strict Whether the equality test should be strict
     * @return BL_CustomGrid_Object
     */
    public function substractData($key, $value, $strict = false)
    {
        if ($this->hasData($key)) {
            if (is_array($data = $this->getData($key))) {
                $subKeys = array_keys($data, $value, $strict);
                
                if (!empty($subKeys)) {
                    foreach ($subKeys as $subKey) {
                        unset($data[$subKey]);
                    }
                    
                    $this->setData($key, $data);
                }
            } else {
                if ((!$strict && ($data == $value)) || ($strict && ($data === $value))) {
                    $this->unsetData($key);
                }
            }
        }
        return $this;
    }
    
    /**
     * Compare the values corresponding to the given data key as-is between this object and the given one
     * 
     * @param string $key Data key
     * @param BL_CustomGrid_Object $object Object with which to compare the given value
     * @return int 1 if the value from this object is greater, -1 if it is lesser, 0 if both values are equal
     */
    public function compareDataTo($key, BL_CustomGrid_Object $object)
    {
        $value = $this->getData($key);
        $otherValue = $object->getData($key);
        return ($value > $otherValue ? 1: ($value < $otherValue ? -1 : 0));
    }
    
    /**
     * Compare the values corresponding to the given data key as integers between this object and the given one
     * 
     * @param string $key Data key
     * @param BL_CustomGrid_Object $object Object with which to compare the given value
     * @return int 1 if the value from this object is greater, -1 if it is lesser, 0 if both values are equal
     */
    public function compareIntDataTo($key, BL_CustomGrid_Object $object)
    {
        $value = (int) $this->getData($key);
        $otherValue = (int) $object->getData($key);
        return ($value > $otherValue ? 1: ($value < $otherValue ? -1 : 0));
    }
    
    /**
     * Compare the values corresponding to the given data key as strings between this object and the given one
     * 
     * @param string $key Data key
     * @param BL_CustomGrid_Object $object Object with which to compare the given value
     * @param bool $withCase Whether string values should be compared case-sensitively
     * @return int 1 if the value from this object is greater, -1 if it is lesser, 0 if both values are equal
     */
    public function compareStringDataTo($key, BL_CustomGrid_Object $object, $withCase = true)
    {
        $value = (string) $this->getData($key);
        $otherValue = (string) $object->getData($key);
        return ($withCase ? strcmp($value, $otherValue) : strcasecmp($value, $otherValue));
    }
    
    /**
     * Sort the values contained in the array corresponding to the given data key
     * (does nothing if the value is not an array)
     *
     * @see sort() and ksort()
     * @param string $key Data key
     * @param int $flags Sorting type flags
     * @param bool $byKey Whether the value should be sorted by key
     * @return BL_CustomGrid_Object
     */
    protected function _sortData($key, $flags = SORT_REGULAR, $byKey = false)
    {
        if (is_array($data = $this->getData($key))) {
            if ($byKey) {
                ksort($data, $flags);
            } else {
                sort($data, $flags);
            }
            $this->setData($key, $data);
        }
        return $this;
    }
    
    /**
     * Sort the values contained in the array corresponding to the given data key
     * (does nothing if the value is not an array)
     * 
     * @see sort()
     * @param string $key Data key
     * @param int $flags Sorting type flags
     * @return BL_CustomGrid_Object
     */
    public function sortData($key, $flags = SORT_REGULAR)
    {
        return $this->_sortData($key, $flags);
    }
    
    /**
     * Sort by key the values contained in the array corresponding to the given data key
     * (does nothing if the value is not an array)
     * 
     * @see ksort()
     * @param string $key Data key
     * @param int $flags Sorting type flags
     * @return BL_CustomGrid_Object
     */
    public function ksortData($key, $flags = SORT_REGULAR)
    {
        return $this->_sortData($key, $flags, true);
    }
    
    /**
     * Explode the given compound data key, return only the valid sub keys
     * 
     * @param string $key Compound data key
     * @return array
     */
    protected function _explodeCompoundKey($key)
    {
        return array_filter(explode('/', $key), create_function('$v', 'return ($v !== "");'));
    }
    
    /**
     * Traverse the object data by following the given keys and by initializing as much array sub-values as needed
     * 
     * @param array $keys Data keys
     * @return array 0 => Whether the data was left untouched because every key already existed
     *               1 => A reference to the value holded by the last key
     */
    protected function _hardTraverseDataKeys(array $keys)
    {
        $data =& $this->_data;
        $isDataUntouched = true;
        
        foreach ($keys as $key) {
            if (!isset($data[$key])
                || !(is_array($data[$key]) || ($data[$key] instanceof Varien_Object))) {
                $isDataUntouched = false;
                $data[$key] = array();
            }
            if ($data[$key] instanceof Varien_Object) {
                $data =& $data[$key]->_data;
            } else {
                $data =& $data[$key];
            }
        }
        
        return array($isDataUntouched, &$data);
    }
    
    /**
     * Traverse the object data by following the given keys and by stopping as soon as a key can not be found
     * 
     * @param array $keys Data keys
     * @return array 0 => Whether the last key was found
     *               1 => If any, the first key that was not found, otherwise the last key
     *               2 => A reference to tshe value holded by the latest key that was found
     */
    protected function _softTraverseDataKeys(array $keys)
    {
        $key = null;
        $data =& $this->_data;
        $isLastKeyFound = true;
        
        foreach ($keys as $key) {
            if (isset($data[$key])
                && (is_array($data[$key]) || ($data[$key] instanceof Varien_Object))) {
                if ($data[$key] instanceof Varien_Object) {
                    $data =& $data[$key]->_data;
                } else {
                    $data =& $data[$key];
                }
            } else {
                $isLastKeyFound = false;
                break;
            }
        }
        
        return array($isLastKeyFound, $key, &$data);
    }
    
    /**
     * Set the value corresponding to the given data key (support compound keys)
     * 
     * @param string $key Data key (can be compound)
     * @param mixed $value Data value
     * @return BL_CustomGrid_Object
     */
    public function setData($key, $value = null)
    {
        if (is_string($key) && (strpos($key, '/') !== false)) {
            $this->_hasDataChanges = true;
            $keys =  $this->_explodeCompoundKey($key);
            $result = $this->_hardTraverseDataKeys($keys);
            $result[1] = $value;
            return $this;
        }
        return parent::setData($key, $value);
    }
    
    /**
     * Unset the value corresponding to the given data key (support compound keys)
     * 
     * @param string $key Data key (can be compound)
     * @return BL_CustomGrid_Object
     */
    public function unsetData($key = null)
    {
        if (is_string($key) && (strpos($key, '/') !== false)) {
            $this->_hasDataChanges = true;
            $keys    =  $this->_explodeCompoundKey($key);
            $lastKey =  array_pop($keys);
            $result  = $this->_softTraverseDataKeys($keys);
            
            if ($result[0] && isset($result[2][$lastKey])) {
                unset($result[2][$lastKey]);
            }
            
            return $this;
        }
        return parent::unsetData($key);
    }
    
    /**
     * Return the value corresponding to the given data key, apply the given default value first if no value is set
     * (support compound keys)
     * 
     * @param string $key Data key
     * @param mixed $default Default value
     * @return mixed
     */
    public function getDataSetDefault($key, $default)
    {
        if (is_string($key) && (strpos($key, '/') !== false)) {
            $keys    =  $this->_explodeCompoundKey($key);
            $lastKey =  array_pop($keys);
            $result  = $this->_hardTraverseDataKeys($keys);
            
            if (!$result[0] || !isset($result[1][$lastKey])) {
                $result[1][$lastKey] = $default;
            }
            
            return $result[1][$lastKey];
        }
        return parent::getDataSetDefault($key, $default);
    }
    
    /**
     * Return the value corresponding to the given data key and index if it is considered true,
     * otherwise return the given default value (support compound keys)
     * 
     * @param string $key Data key
     * @param mixed $index Data index
     * @param mixed $default Default value
     * @return mixed
     */
    public function getNotEmptyData($key, $index = null, $default = null)
    {
        return (($data = $this->getData($key, $index)) ? $data : $default);
    }
    
    /**
     * If a data key is given, return whether a value is set for this key,
     * otherwise return whether this object is not empty
     * (support compound keys)
     * 
     * @param string $key Data key (can be compound)
     * @return bool
     */
    public function hasData($key = '')
    {
        if (is_string($key) && (strpos($key, '/') !== false)) {
            $keys    =  $this->_explodeCompoundKey($key);
            $lastKey =  array_pop($keys);
            $result  = $this->_softTraverseDataKeys($keys);
            
            if ($result[0]) {
                $found = array_key_exists($lastKey, $result[2]);
            } else {
                $found = false;
            }
            
            return $found;
        }
        return parent::hasData($key);
    }
    
    public function __call($method, $args)
    {
        if (substr($method, 0, 2) == 'is') {
            $key = $this->_underscore(substr($method, 2));
            $result = false;
            
            if (array_key_exists('is_' . $key, $this->_data)) {
                $result = (bool) $this->_data['is_' . $key];
            } elseif (array_key_exists($key, $this->_data)) {
                $result = (bool) $this->_data[$key];
            }
            
            return $result;
        }
        return parent::__call($method, $args);
    }
}
