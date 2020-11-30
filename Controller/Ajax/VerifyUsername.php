<?php

namespace Fastest\CustomerRegister\Controller\Ajax;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;

class VerifyUsername extends Action
{
    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @param Context $context
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        Context $context,
        ResultFactory $resultFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    )
    {
        $this->resultFactory = $resultFactory;
        $this->resultJsonFactory = $resultJsonFactory;

        parent::__construct($context);
    }

    public function execute()
    {

        $resource = $this->_objectManager->create('\Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);

        $resultJson = $this->resultJsonFactory->create();
        $response = [
            'success' => '',
            'message' => ''
        ];

        $username_customer = $this->getRequest()->getParam('username_customer');

        // Check exist username
        $sql_get_attribute_id_username = "select attribute_id from eav_attribute where attribute_code = 'username_customer'";
        $attribute_id_username = $connection->fetchOne($sql_get_attribute_id_username);
        $sql_get_custom_attribute = "SELECT * FROM customer_entity_varchar where attribute_id = " . $attribute_id_username . " and value = '". $username_customer . "'";
        $value_attribute_username = $connection->fetchAll($sql_get_custom_attribute);

        // If exist username
        if (!empty($value_attribute_username)) {
            $response['success'] = false;
            $response['message'] ='Tên tài khoản đã được sử dụng, vui lòng chọn tên khác.';
        }else{
            $response['success'] = true;
            $response['message'] ='Success.';
        }

        $resultJson->setData($response);
        return $resultJson;
    }
}

