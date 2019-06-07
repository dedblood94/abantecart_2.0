<?php
/**
 * AbanteCart, Ideal Open Source Ecommerce Solution
 * http://www.abantecart.com
 *
 * Copyright 2011-2018 Belavier Commerce LLC
 *
 * This source file is subject to Open Software License (OSL 3.0)
 * License details is bundled with this package in the file LICENSE.txt.
 * It is also available at this URL:
 * <http://www.opensource.org/licenses/OSL-3.0>
 *
 * UPGRADE NOTE:
 * Do not edit or add to this file if you wish to upgrade AbanteCart to newer
 * versions in the future. If you wish to customize AbanteCart for your
 * needs please refer to http://www.abantecart.com for more information.
 */

namespace unit\models;

use abc\core\ABC;
use abc\models\BaseModel;
use abc\models\catalog\CategoriesToStore;
use abc\models\catalog\Category;
use abc\models\catalog\CategoryDescription;
use abc\models\catalog\Download;
use abc\models\catalog\DownloadAttributeValue;
use abc\models\catalog\DownloadDescription;
use abc\models\catalog\Manufacturer;
use abc\models\catalog\ManufacturersToStore;
use abc\models\catalog\ProductDescription;
use abc\models\catalog\ProductDiscount;
use abc\models\catalog\ProductFilter;
use abc\models\catalog\ProductFilterDescription;
use abc\models\catalog\ProductFilterRange;
use abc\models\catalog\ProductFilterRangesDescription;
use abc\models\catalog\ProductOption;
use abc\models\catalog\ProductOptionDescription;
use abc\models\catalog\ProductOptionValue;
use abc\models\catalog\ProductOptionValueDescription;
use abc\models\catalog\ProductsFeatured;
use abc\models\catalog\ProductSpecial;
use abc\models\catalog\ProductsRelated;
use abc\models\catalog\ProductTag;
use abc\models\customer\Customer;
use abc\models\customer\CustomerCommunication;
use abc\models\customer\CustomerGroup;
use abc\models\customer\CustomerNotification;
use abc\models\customer\CustomerTransaction;
use abc\models\customer\OnlineCustomer;
use abc\tests\unit\ATestCase;
use abc\models\catalog\Product;
use abc\tests\unit\modules\listeners\ATestListener;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Warning;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

class BaseModelTest extends ATestCase
{

    public function testValidationPassed()
    {
        $result = false;
        $productId = null;
        $arProduct = [
            'status'              => '1',
            'featured'            => '1',
            'product_description' =>
                [
                    'name'             => 'Test product',
                    'blurb'            => 'Test blurb',
                    'description'      => 'Test description',
                    'meta_keywords'    => '',
                    'meta_description' => '',
                    'language_id'      => 1,
                ],
            'product_tags'        => 'cheeks,makeup',
            'product_category'    =>
                [
                    0 => '40',
                ],
            'product_store'       =>
                [
                    0 => '0',
                ],
            'manufacturer_id'     => '11',
            'model'               => 'valid model',
            'call_to_order'       => '0',
            'price'               => '29.5000',
            'cost'                => '22',
            'tax_class_id'        => '1',
            'subtract'            => '0',
            'quantity'            => '99',
            'minimum'             => '1',
            'maximum'             => '0',
            'stock_checkout'      => '',
            'stock_status_id'     => '1',
            'sku'                 => '124596788',
            'location'            => 'location-max:128',
            'keyword'             => '',
            'date_available'      => '2013-08-29 14:35:30',
            'sort_order'          => '1',
            'shipping'            => '1',
            'free_shipping'       => '0',
            'ship_individually'   => '0',
            'shipping_price'      => '0',
            'length'              => '0.00',
            'width'               => '0.00',
            'height'              => '0.00',
            'length_class_id'     => '0',
            'weight'              => '75.00',
            'weight_class_id'     => '2',
        ];
        try {
            $productId = Product::createProduct($arProduct);
            $result = true;
        } catch (\PDOException $e) {
            $this->fail($e->getMessage());
        } catch (Warning $e) {
            $this->fail($e->getMessage());
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }

        $this->assertIsInt($productId);

        return $productId;
    }

    public function testValidationNotPassed()
    {

        $result = false;

        try {
            $product = new Product(
                [
                    'model'          => 'invalid',
                    'sku'            => null,
                    'location'       => 'max:1280000000000000000000000000000000000000000'
                        .'00000000000000000000000000000000000000000000000000000000000000000000000000000000000',
                    'quantity'       => 'a',
                    'shipping_price' => '$45.12000',
                ]
            );

            $product->save();
            $result = true;
        } catch (\PDOException $e) {
            $this->fail($e->getMessage());
        } catch (ValidationException $e) {
            $error_text = $e->getMessage();
            $result = true;
        } catch (\Exception $e) {
            $error_text = $e->getMessage();
            if (is_int(strpos($error_text, "'validation' =>"))) {
                echo $e->getMessage();
                $result = true;
            } else {
                $this->fail($e->getMessage());
            }

        }
        $this->assertEquals(true, $result);
    }

    /**
     * @depends testValidationPassed
     */
    public function testEventOnSaved($productId)
    {
        $model = new Product();
        $product = $model->find($productId);

        try {
            $product->fill(
                [
                    'model' => 'testmodel',
                    'sku'   => '124596788',
                ]
            );
            $product->save();
        } catch (\PDOException $e) {
            $this->fail($e->getMessage());
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }

        $this->assertEquals(
            ATestListener::class,
            $this->registry->get('handler test result')
        );
    }

    /**
     * @depends testValidationPassed
     */
    public function testSoftDelete($productId)
    {
        $model = new Product();
        $product = $model->find($productId);
        $result = false;

        if ($product) {
            $product->delete();
            Product::onlyTrashed()->where('product_id', $productId)->restore();
            try {
                $product->get(['date_deleted']);
                $result = true;
            } catch (\PDOException $e) {
                $this->fail($e->getTraceAsString());
            }
        }
        $this->assertEquals($result, true);

        if ($result) {
            //test force deleting
            $env = ABC::env('MODEL');
            $env['FORCE_DELETING'][Product::class] = true;
            ABC::env('MODEL', $env, true);
            $model = new Product();
            $product = $model->find($productId);
            $product->delete();
            $exists = Product::onlyTrashed()->where('product_id', $productId)->exists();
            $this->assertEquals($exists, false);
        }

    }

    public function testGetMainEntity()
    {
        /*$directory = new RecursiveDirectoryIterator(ABC::env('DIR_ROOT').'abc/models/');
        $iterator = new RecursiveIteratorIterator($directory);
        $php_files = new RegexIterator($iterator, '/^(.*\/)(([A-Z])([a-zA-Z0-9]+))+(.php)$/', RecursiveRegexIterator::GET_MATCH);
        foreach ($php_files as $item) {
            if (is_array($item) && isset($item[2])) {
                include_once $item[0];
                $className = $item[2];
                if ($className == 'BaseModel') {
                    continue;
                }
                $classInstance = new $className;
                if ($classInstance instanceof BaseModel) {
                    $entity = $classInstance->getMainEntity();
                    \H::df($entity);
                }
            }
        }*/

        $checkClasses = [
            //Tested Model                           Expected class
            CategoriesToStore::class              => Category::class,
            Category::class                       => Category::class,
            CategoryDescription::class            => Category::class,
            Manufacturer::class                   => Manufacturer::class,
            ManufacturersToStore::class           => Manufacturer::class,
            Product::class                        => Product::class,
            ProductDescription::class             => Product::class,
            ProductDiscount::class                => Product::class,
            ProductFilter::class                  => false,
            ProductFilterDescription::class       => false,
            ProductFilterRange::class             => false,
            ProductFilterRangesDescription::class => false,
            ProductOption::class                  => Product::class,
            ProductOptionDescription::class       => Product::class,
            ProductOptionValue::class             => Product::class,
            ProductOptionValueDescription::class  => Product::class,
            ProductsFeatured::class               => Product::class,
            ProductSpecial::class                 => Product::class,
            ProductsRelated::class                => Product::class,
            ProductTag::class                     => Product::class,
            Customer::class                       => Customer::class,
            CustomerCommunication::class          => Customer::class,
            CustomerGroup::class                  => Customer::class,
            CustomerNotification::class           => Customer::class,
            CustomerTransaction::class            => Customer::class,
            OnlineCustomer::class                 => false,
        ];

        foreach ($checkClasses as $checkClass => $expectedClass) {
            $entity = (new $checkClass())->getMainEntity();
            /*if (!$entity) {
                \H::df($checkClass);
                \H::df($expectedClass);
            }*/
            $this->assertEquals($expectedClass, $entity);
        }

    }

}
