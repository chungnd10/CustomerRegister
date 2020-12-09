<?php

namespace Fastest\CustomerRegister\Setup;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Customer\Model\ResourceModel\Attribute;
use Magento\Eav\Model\Config;

class UpgradeData implements UpgradeDataInterface
{
    protected $customerSetupFactory;

    private $attributeSetFactory;

    private $eavSetupFactory;

    private $eavConfig;

    private $attributeResource;


    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory,
        EavSetupFactory $eavSetupFactory,
        Attribute $attributeResource,
        Config $eavConfig
    )
    {
        $this->eavConfig = $eavConfig;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeResource = $attributeResource;
    }


    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.6.0') < 0) {

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->removeAttribute(Customer::ENTITY, "agency_note");
            $attributeSetId = $eavSetup->getDefaultAttributeSetId(Customer::ENTITY);
            $attributeGroupId = $eavSetup->getDefaultAttributeGroupId(Customer::ENTITY);
            $eavSetup->addAttribute(Customer::ENTITY, 'agency_note', [
                // Attribute parameters
                'type' => 'varchar',
                'label' => 'Agency Note',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'sort_order' => 800,
                'position' => 800,
                'system' => 0
            ]);

            $agency_note = $this->eavConfig->getAttribute(Customer::ENTITY, 'agency_note');
            $agency_note->setData('attribute_set_id', $attributeSetId);
            $agency_note->setData('attribute_group_id', $attributeGroupId);
            $agency_note->setData('used_in_forms', [
                'adminhtml_customer',
                'customer_account_create',
                'customer_account_edit'
            ]);
            $this->attributeResource->save($agency_note);

        }

        $setup->endSetup();
    }
}