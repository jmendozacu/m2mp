<?php
namespace Polcode\RegistrationNewFields\Setup;


use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * Customer setup factory
     *
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;
    
    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * Init
     *
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
            \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory,
            AttributeSetFactory $attributeSetFactory)
    {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        
        $installer->startSetup();
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
        
        $attributesInfo = [
            'company_name' => [
                'label' => 'Company name',
                'type' => 'varchar',
                'input' => 'text',
                'visible' => true,
                'required' => false,
                'default' => "",
                'frontend' => "",
            ],
            'vat_number' => [
                'label' => 'Vat number',
                'type' => 'varchar',
                'input' => 'text',
                'visible' => true,
                'required' => false,
                'default' => "",
            ]
        ];

        foreach ($attributesInfo as $attributeCode => $attributeParams) {
            $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, $attributeCode, $attributeParams);
            
            $attribute = $customerSetup->getEavConfig()->getAttribute(\Magento\Customer\Model\Customer::ENTITY, $attributeCode);
            $used_in_forms = [];
            $used_in_forms[] = "adminhtml_customer";
            $used_in_forms[] = "checkout_register";
            $used_in_forms[] = "customer_account_create";
            $used_in_forms[] = "customer_account_edit";
            $used_in_forms[] = "adminhtml_checkout";
            
            $attribute->setData("used_in_forms", $used_in_forms)
                    ->setData("is_used_for_customer_segment", true)
                    ->setData("is_system", 0)
                    ->setData("is_user_defined", 1)
                    ->setData("is_visible", 1)
                    ->setData("sort_order", 100);
            
            $attribute->save();           
        }
        
        $setup->endSetup();
    }
}
