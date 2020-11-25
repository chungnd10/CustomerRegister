<?php
namespace Fastest\CustomerRegister\Setup;

use Magento\Customer\Model\Customer;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements \Magento\Framework\Setup\InstallDataInterface
{
    private $eavSetupFactory;

    private $eavConfig;

    private $attributeResource;

    public function __construct(
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Customer\Model\ResourceModel\Attribute $attributeResource
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->attributeResource = $attributeResource;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->removeAttribute(Customer::ENTITY, "username_customer");

        $attributeSetId = $eavSetup->getDefaultAttributeSetId(Customer::ENTITY);
        $attributeGroupId = $eavSetup->getDefaultAttributeGroupId(Customer::ENTITY);

        $eavSetup->addAttribute(Customer::ENTITY, 'username_customer', [
            // Attribute parameters
            'type' => 'varchar',
            'label' => 'Username ',
            'input' => 'text',
            'required' => true,
            'visible' => true,
            'user_defined' => true,
            'sort_order' => 700,
            'position' => 700,
            'system' => 0,
            'unique' => true,
        ]);

        $username = $this->eavConfig->getAttribute(Customer::ENTITY, 'username_customer');
        $username->setData('attribute_set_id', $attributeSetId);
        $username->setData('attribute_group_id', $attributeGroupId);
        /*
        //You can use this attribute in the following forms
        adminhtml_checkout
        adminhtml_customer
        adminhtml_customer_address
        customer_account_create
        customer_account_edit
        customer_address_edit
        customer_register_address
        */

        $username->setData('used_in_forms', [
            'adminhtml_customer',
            'customer_account_create',
            'customer_account_edit'
        ]);

        $this->attributeResource->save($username);

        //------------------------------------------------------------------------------------

        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->removeAttribute(Customer::ENTITY, "phone_number");

        $attributeSetId = $eavSetup->getDefaultAttributeSetId(Customer::ENTITY);
        $attributeGroupId = $eavSetup->getDefaultAttributeGroupId(Customer::ENTITY);

        $eavSetup->addAttribute(Customer::ENTITY, 'phone_number', [
            // Attribute parameters
            'type' => 'varchar',
            'label' => 'Phone number',
            'input' => 'text',
            'required' => true,
            'visible' => true,
            'user_defined' => true,
            'sort_order' => 800,
            'position' => 800,
            'system' => 0,
            'unique' => true,
        ]);

        $phone_number = $this->eavConfig->getAttribute(Customer::ENTITY, 'phone_number');
        $phone_number->setData('attribute_set_id', $attributeSetId);
        $phone_number->setData('attribute_group_id', $attributeGroupId);

        $phone_number->setData('used_in_forms', [
            'adminhtml_customer',
            'customer_account_create',
            'customer_account_edit'
        ]);

        $this->attributeResource->save($phone_number);

        //------------------------------------------------------------------------------------
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->removeAttribute(Customer::ENTITY, "tax_code");

        $attributeSetId = $eavSetup->getDefaultAttributeSetId(Customer::ENTITY);
        $attributeGroupId = $eavSetup->getDefaultAttributeGroupId(Customer::ENTITY);

        $eavSetup->addAttribute(Customer::ENTITY, 'tax_code', [
            // Attribute parameters
            'type' => 'varchar',
            'label' => 'Tax Code',
            'input' => 'text',
            'required' => false,
            'visible' => true,
            'user_defined' => true,
            'sort_order' => 900,
            'position' => 900,
            'system' => 0,
            'unique' => true,
        ]);

        $tax_code = $this->eavConfig->getAttribute(Customer::ENTITY, 'tax_code');
        $tax_code->setData('attribute_set_id', $attributeSetId);
        $tax_code->setData('attribute_group_id', $attributeGroupId);
        $tax_code->setData('used_in_forms', [
            'adminhtml_customer',
            'customer_account_create',
            'customer_account_edit'
        ]);

        $this->attributeResource->save($tax_code);

    }
}
