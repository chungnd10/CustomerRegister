<?php

namespace HomeOrigin\Custom\Helper;
use \Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    private $_objectManager;
    protected $connection;
    protected $_storeManager;
    protected $_resource;
    public $registry;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Helper\Context $context
    )
    {
        $this->_objectManager = $objectManager;
        $this->_resource = $this->_objectManager->create('Magento\Framework\App\ResourceConnection');
        $this->_storeManager = $storeManager;
        $this->registry = $registry;
        parent::__construct($context);
    }

    public function checkExistCustomAttribute($custom_attribute)
    {
        $this->connection = $this->_resource->getConnection();
        $table_name = $this->_resource->getTableName('marketplace_product');
        $sql = $this->connection->select()
                                ->from($table_name)
                                ->where('attribute_code = ?', $custom_attribute);

        $result = $this->connection->fetchOne($sql);
        return $result;
    }


}
