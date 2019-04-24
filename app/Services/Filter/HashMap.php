<?php

namespace App\Services\Filter;

class HashMap
{
    /**
     * 哈希表变量.
     *
     * @var array|null
     */
    protected $hashTable = [];

    /**
     * 向 HashMap 中添加一个键值对.
     *
     * @param  [type] $key   [description]
     * @param  [type] $value [description]
     *
     * @return mixed|null
     */
    public function put($key, $value)
    {
        if (!array_key_exists($key, $this->hashTable)) {
            $this->hashTable[$key] = $value;

            return null;
        }

        $_temp = $this->hashTable[$key];

        $this->hashTable[$key] = $value;

        return $_temp;
    }

    /**
     * 根据 key 获取对应的 value.
     *
     * @param  [type] $key [description]
     *
     * @return mixed|null
     */
    public function get($key)
    {
        if (array_key_exists($key, $this->hashTable)) {
            return $this->hashTable[$key];
        }

        return null;
    }

    /**
     * 删除指定 key 的键值对.
     *
     * @param  [type] $key [description]
     *
     * @return mixed|null
     */
    public function remove($key)
    {
        $temp_table = [];

        if (array_key_exists($key, $this->hashTable)) {
            $tempValue = $this->hashTable[$key];

            while ($curValue = current($this->hashTable)) {
                if (!(key($this->hashTable) == $key)) {
                    $temp_table[key($this->hashTable)] = $curValue;
                }

                next($this->hashTable);
            }

            $this->hashTable = null;
            $this->hashTable = $temp_table;

            return $tempValue;
        }
    }

    /**
     * 获取 HashMap 的所有键值
     *
     * @return array
     */
    public function keys()
    {
        return array_keys($this->hashTable);
    }

    /**
     * 获取 HashMap 的所有 value 值
     *
     * @return array
     */
    public function values()
    {
        return array_values($this->hashTable);
    }

    /**
     * 将一个 HashMap 的值全部 put 到当前的 HashMap 中
     *
     * @param  HashMap $map
     *
     * @return bool
     */
    public function putAll($map)
    {
        if (!$map->isEmpty() && $map->size() > 0) {
            $keys = $map->keys();

            foreach ($keys as $key) {
                $this->put($key, $map->get($key));
            }
        }

        return true;
    }

    /**
     * 移除 HashMap 中所有元素.
     *
     * @return bool
     */
    public function removeAll()
    {
        $this->hashTable = null;

        return true;
    }

    /**
     * 判断 HashMap 中是否包含指定的值.
     *
     * @param $value
     *
     * @return bool
     */
    public function containsValue($value)
    {
        while ($curValue = current($this->hashTable)) {
            if ($curValue == $value) {
                return true;
            }

            next($this->hashTable);
        }

        return false;
    }

    /**
     * 判断 HashMap 中是否包含指定的键 key.
     *
     * @param $key
     *
     * @return bool
     */
    public function containsKey($key)
    {
        return array_key_exists($key, $this->hashTable);
    }

    /**
     * 获取 HashMap 中元素个数
     *
     * @return int
     */
    public function size()
    {
        return count($this->hashTable);
    }

    /**
     * 判断 HashMap 是否为空.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return 0 == count($this->hashTable);
    }
}
