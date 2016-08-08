<?php

namespace Muffin\Webservice\Association;

/**
 * Represents a type of association that that needs to be recovered by performing
 * an extra query.
 */
trait ExternalAssociationTrait
{
    use QueryableAssociationTrait {
        _defaultOptions as private _selectableOptions;
    }

    /**
     * Order in which target records should be returned
     *
     * @var mixed
     */
    protected $_sort;

    /**
     * Whether this association can be expressed directly in a query join
     *
     * @param array $options custom options key that could alter the return value
     * @return bool if the 'matching' key in $option is true then this function
     * will return true, false otherwise
     */
    public function canBeJoined(array $options = [])
    {
        return !empty($options['matching']);
    }

    /**
     * Sets the name of the field representing the foreign key to the source endpoint.
     * If no parameters are passed current field is returned
     *
     * @param string|null $key the key to be used to link both endpoints together
     * @return string
     */
    public function foreignKey($key = null)
    {
        if ($key === null) {
            if ($this->_foreignKey === null) {
                $this->_foreignKey = $this->_modelKey($this->source()->endpoint());
            }
            return $this->_foreignKey;
        }
        return parent::foreignKey($key);
    }

    /**
     * Sets the sort order in which target records should be returned.
     * If no arguments are passed the currently configured value is returned
     *
     * @param mixed $sort A find() compatible order clause
     * @return mixed
     */
    public function sort($sort = null)
    {
        if ($sort !== null) {
            $this->_sort = $sort;
        }
        return $this->_sort;
    }

    /**
     * {@inheritDoc}
     */
    public function defaultRowValue($row, $joined)
    {
        $sourceAlias = $this->source()->alias();
        if (isset($row[$sourceAlias])) {
            $row[$sourceAlias][$this->property()] = $joined ? null : [];
        }
        return $row;
    }

    /**
     * Returns the default options to use for the eagerLoader
     *
     * @return array
     */
    protected function _defaultOptions()
    {
        return $this->_selectableOptions() + [
            'sort' => $this->sort()
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function _buildResultMap($fetchQuery, $options)
    {
        $resultMap = [];
        $key = (array)$options['foreignKey'];

        foreach ($fetchQuery->all() as $result) {
            $values = [];
            foreach ($key as $k) {
                $values[] = $result[$k];
            }
            $resultMap[implode(';', $values)][] = $result;
        }
        return $resultMap;
    }

    /**
     * Parse extra options passed in the constructor.
     *
     * @param array $opts original list of options passed in constructor
     * @return void
     */
    protected function _options(array $opts)
    {
        if (isset($opts['sort'])) {
            $this->sort($opts['sort']);
        }
    }
}