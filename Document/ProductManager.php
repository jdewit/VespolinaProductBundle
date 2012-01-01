<?php
/**
 * (c) Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Vespolina\ProductBundle\Document;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\DependencyInjection\Container;

use Vespolina\ProductBundle\Document\Product;
use Vespolina\ProductBundle\Model\ProductInterface;
use Vespolina\ProductBundle\Model\ProductManager as BaseProductManager;
/**
 * @author Richard Shank <develop@zestic.com>
 */
class ProductManager extends BaseProductManager
{
    protected $dm;
    protected $productClass;
    protected $productRepo;

    public function __construct(DocumentManager $dm, $productClass, $identifiers, $identifierSetClass, $mediaManager = null)
    {
        $this->dm = $dm;
        $this->productClass = $productClass;
        $this->productRepo = $this->dm->getRepository($productClass);
        parent::__construct($identifiers, $identifierSetClass, $mediaManager);
    }

    /**
     * @inheritdoc
     */
    public function createProduct()
    {
        // TODO: this will be using factories to allow for a number of different types of product classes
        $product = new $this->productClass($this->identifierSetClass);
        return $product;
    }

    /**
     * @inheritdoc
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->productRepo->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * @inheritdoc
     */
    public function findProductById($id)
    {
        if ($product = $this->productRepo->find($id)) {
            $rp = new \ReflectionProperty($product, 'identifierSetClass');
            $rp->setAccessible(true);
            $rp->setValue($product, $this->identifierSetClass);

            return $product;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function findProductByIdentifier($name, $code)
    {

    }

    /**
     * @inheritdoc
     */
    public function findProductByName($name)
    {
        $products = array();
        if (!$results = $this->productRepo->findBy(array('name' => $name))) {

            return null;
        }

        $rp = new \ReflectionProperty($this->productClass, 'identifierSetClass');
        $rp->setAccessible(true);

        foreach ($results as $product) {
            $rp->setValue($product, $this->identifierSetClass);
        }

        if ($results->count() === 1) {
            return $results->current();
        }

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function updateProduct(ProductInterface $product, $andFlush = true)
    {
        $this->dm->persist($product);
        if ($andFlush) {
            $this->dm->flush();
        }
    }
}
