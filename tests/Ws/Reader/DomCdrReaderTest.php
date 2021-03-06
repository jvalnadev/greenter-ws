<?php
/**
 * Created by PhpStorm.
 * User: Giansalex
 * Date: 22/07/2017
 * Time: 16:29
 */

namespace Tests\Greenter\Ws\Reader;

use Greenter\Ws\Reader\DomCdrReader;
use Greenter\Ws\Reader\XmlReader;

/**
 * Class DomCdrReaderTest
 * @package Tests\Greenter\Ws\Reader
 */
class DomCdrReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @throws \Exception
     */
    public function testGetResponse()
    {
        $path = __DIR__ . '/../../Resources/R-20600995805-01-F001-1.xml';
        $xml = file_get_contents($path);
        $reader = $this->getCdrReader();
        $cdr = $reader->getCdrResponse($xml);

        $this->assertNotEmpty($cdr);
        $this->assertEquals(0, count($cdr->getNotes()));
        $this->assertEquals('F001-1', $cdr->getId());
        $this->assertEquals('0', $cdr->getCode());
        $this->assertEquals('La Factura numero F001-00000001, ha sido aceptada', $cdr->getDescription());
    }

    /**
     * @throws \Exception
     */
    public function testGetResponseWithNotes()
    {
        $path = __DIR__ . '/../../Resources/R-20600995805-01-F001-3.xml';
        $xml = file_get_contents($path);
        $reader = $this->getCdrReader();
        $cdr = $reader->getCdrResponse($xml);

        $this->assertNotEmpty($cdr);
        $this->assertEquals(2, count($cdr->getNotes()));
    }

    public function testNotFoundResponse()
    {
        $xml = <<<XML
<ar:AppRespnse xmlns:ar="urn:oasis:names:specification:ubl:schema:xsd:ApplicationResponse-2"
xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2">
    <cac:item>Empty</cac:item>
    <cbc:value>1</cbc:value>
</ar:AppRespnse>
XML;
        $reader = $this->getCdrReader();
        $cdr = $reader->getCdrResponse($xml);

        $this->assertEmpty($cdr->getId());
        $this->assertEmpty($cdr->getCode());
        $this->assertEmpty($cdr->getDescription());
        $this->assertEmpty($cdr->getNotes());
    }

    /**
     * @throws \Exception
     */
    public function testEmptyNodes()
    {
        $doc = new \DOMDocument();
        $doc->load(__DIR__ . '/../../Resources/R-20600995805-01-F001-3.xml');
        $referenceId = $doc->documentElement
                        ->childNodes->item(27)
                        ->childNodes->item(1)
                        ->childNodes->item(1);

        $referenceId->parentNode->removeChild($referenceId);
        $xml = $doc->saveXML();
        $reader = $this->getCdrReader();
        $cdr = $reader->getCdrResponse($xml);

        $this->assertEmpty($cdr->getId());
    }

    private function getCdrReader()
    {
        return new DomCdrReader(new XmlReader());
    }
}