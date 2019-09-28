<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    // protected $base_url;

    public function __construct(array $parameters = [])
    {
        // $this->base_url = $base_url;
        // $this->parameters = $parameters;
        // append data
        foreach ($parameters as $key => $value) {
            $this->{$key}   = $value;
        }
    }

    /**
     * Helper to make request to API using cUrl
     */
    public function parseAPIRequest($requestURI) {
        //break requestURI into two parts
        $uriParts = explode(' ', $requestURI);

        // at least gotta be 2 parts
        if (count($uriParts) != 2) {
            throw new InvalidArgumentException("Request URI must consists of METHOD and URI");
        }

        // simply join the base_url . $uriParts[1]
        $url = $this->base_url . $uriParts[1];

        return [
            'METHOD'    => $uriParts[0],
            'URL'       => $url
        ];
    }

    /**
     * @When I request :requestURI
     */
    public function iRequest($requestURI)
    {
        echo "Requestdata : \n";
        
        $requestData = $this->parseAPIRequest($requestURI);
        print_r($requestData);
        // send request and store for next
        // assertion


        return true;
    }

    /**
     * @Then I get :arg1 response
     */
    public function iGetResponse($arg1)
    {
        throw new \Exception("NOT IMPLEMENTED YET");
    }

    /**
     * @Then scope into the :arg1 property
     */
    public function scopeIntoTheProperty($arg1)
    {
        throw new PendingException("NO SCOPING YET");
    }

    /**
     * @Then the properties exist:
     */
    public function thePropertiesExist(PyStringNode $string)
    {
        throw new PendingException();
    }

    /**
     * @Then the :arg1 property is an integer
     */
    public function thePropertyIsAnInteger($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Then the :arg1 property is an array
     */
    public function thePropertyIsAnArray($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Then the :arg1 property contains at least :arg2 item
     */
    public function thePropertyContainsAtLeastItem($arg1, $arg2)
    {
        throw new PendingException();
    }

    /**
     * @Then the :arg1 property contains :arg2 item
     */
    public function thePropertyContainsItem($arg1, $arg2)
    {
        throw new PendingException();
    }

    /**
     * @Given I have the payload:
     */
    public function iHaveThePayload(PyStringNode $string)
    {
        throw new PendingException();
    }

    /**
     * @Given I use the token :arg1
     */
    public function iUseTheToken($arg1)
    {
        throw new PendingException();
    }
}
