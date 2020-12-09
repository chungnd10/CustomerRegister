<?php

namespace Fastest\CustomerRegister\Controller\Ajax;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;

class VerifyPhonenumber extends Action
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

        $phone_number = $this->getRequest()->getParam('phone_number');

        $pattern = "/(02|03|05|07|08|09)+([0-9]{8,9})\b/";

        if (preg_match($pattern, $phone_number)) {
            $sql_get_attribute_id_phone_number = "select attribute_id from eav_attribute where attribute_code = 'phone_number'";
            $attribute_id_phone_number = $connection->fetchOne($sql_get_attribute_id_phone_number);
            $sql_get_custom_attribute = "SELECT * FROM customer_entity_varchar where attribute_id = " . $attribute_id_phone_number . " and value = '" . $phone_number . "'";
            $value_attribute_phone_number = $connection->fetchAll($sql_get_custom_attribute);

            // If exist username
            if (!empty($value_attribute_phone_number)) {
                $response['success'] = 'false';
                $response['message'] = 'Số điện thoại đã được sử dụng, vui lòng chọn số điện thoại khác';
            } else {
                $response['success'] = 'true';
                $response['message'] = 'Success.';
            }

            $resultJson->setData($response);
            return $resultJson;
        }else {
            $response['success'] = 'none';
            $response['message'] = 'Value incorrect format';

            $resultJson->setData($response);
            return $resultJson;
        }
    }
}

