<?php
namespace abc\tests\unit;

use abc\models\locale\CountryDescription;
use Illuminate\Validation\ValidationException;

/**
 * Class CountryDescriptionModelTest
 */
class CountryDescriptionModelTest extends ATestCase{


    public function testValidator()
    {

        $country = new CountryDescription(
            [
                'name' => 43647890965,
                'language_id' => 'fvf',
                ]
        );
        $errors = [];
        try {
            $country->validate();
        } catch (ValidationException $e) {
            $errors =$country->errors()['validation'];
        }


        $this->assertEquals(2, count($errors));


        $country= new CountryDescription(
            [
                'name' => 'somestring',
                'language_id' => 1,
            ]
        );
        $errors = [];
        try {
            $country->validate();
        } catch (ValidationException $e) {
            $errors = $country->errors()['validation'];
        }

        $this->assertEquals(0, count($errors));

    }
}