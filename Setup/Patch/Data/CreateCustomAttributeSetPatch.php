<?php
namespace Sb\CustomAttributeSource\Setup\Patch\Data;

use Exception;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Psr\Log\LoggerInterface;


class CreateCustomAttributeSetPatch implements DataPatchInterface
{
    private const ATTRIBUTE_SET_SORT_ORDER = 100;

    /**
     * @var ModuleDataSetupInterface
     */
   private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
   private EavSetupFactory $eavSetupFactory;

    /**
     * @var SetFactory
     */
   private SetFactory $attributeSetFactory;

    /**
     * @var LoggerInterface
     */
   private LoggerInterface $logger;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param SetFactory $attributeSetFactory
     * @param LoggerInterface $logger
     */
   public function __construct(
       ModuleDataSetupInterface $moduleDataSetup,
       EavSetupFactory $eavSetupFactory,
       SetFactory $attributeSetFactory,
       LoggerInterface $logger
   ) {
       $this->moduleDataSetup = $moduleDataSetup;
       $this->eavSetupFactory = $eavSetupFactory;
       $this->attributeSetFactory = $attributeSetFactory;
       $this->logger = $logger;
   }

    /**
     * Creating new attribute set
     *
     * @return $this|CreateCustomAttributeSetPatch
     * @throws Exception
     */
   public function apply():self
   {
       try {
           $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

           $attributeSet = $this->attributeSetFactory->create();
           $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
           $attributeSetId = $eavSetup->getDefaultAttributeSetId($entityTypeId);

           $data = [
               'attribute_set_name' => 'Custom Attribute Set',
               'entity_type_id' => $entityTypeId,
               'sort_order' => self::ATTRIBUTE_SET_SORT_ORDER,
           ];

           $attributeSet->setData($data);
           $attributeSet->validate();
           $attributeSet->save();
           $attributeSet->initFromSkeleton($attributeSetId);
           $attributeSet->save();

       } catch (LocalizedException|Exception $ex) {
           $this->logger->error("Something is wrong while creating Custom Attribute Set ". $ex->getMessage());
       }

       return $this;
   }

    /**
     * @inheirtDoc
     */
   public static function getDependencies():array
   {
       return [];
   }

    /**
     * @inheirtDoc
     */
   public function getAliases():array
   {
       return [];
   }

}
