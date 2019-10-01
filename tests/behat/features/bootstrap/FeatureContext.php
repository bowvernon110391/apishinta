<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

use GuzzleHttp\Client;
use Illuminate\Validation\UnauthorizedException;

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

        // try to load guzzle
        $this->client = new Client([
            'base_uri'  => $this->base_url,
            'timeout'   => 5
        ]);

        // headers always exist
        $this->requestOptions = ['exceptions' => FALSE, 'headers'=>[]];
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
            'URL'       => $url,
            'URI'       => $uriParts[1]
        ];
    }

    /**
     * Helper to get current data scope
     */
    public function getCurrentScope() {
        $this->dataScope = $this->dataScope ?? json_decode($this->response->getBody());
        return $this->dataScope;
    }

    public function resetCurrentScope() {
        $this->dataScope = json_decode($this->response->getBody());
    }

    /**
     * @When I request :requestURI
     */
    public function iRequest($requestURI)
    {
        // echo "Requestdata : \n";
        
        $requestData = $this->parseAPIRequest($requestURI);
        // print_r($requestData);
        // send request and store for next
        // assertion

        // grab token

        // store response and call using request Headers
        // echo "request with header: \n";
        // print_r($this->requestOptions);

        // store response
        $this->response = $this->client->request($requestData['METHOD'], $requestData['URI'], $this->requestOptions);
        

        // var_dump($this->response);
        echo "Got response status: " . $this->response->getStatusCode() . "\n";
        echo "Got response body:\n" . $this->response->getBody(). "\n";

        // reset scope
        $this->resetCurrentScope();

        return true;
    }

    /**
     * @Then I get :httpCode response
     */
    public function iGetResponse($httpCode)
    {
        if (!$this->response) {
            throw new \Exception("No response received from server");
        }

        // just compare http code
        if( $this->response->getStatusCode() != $httpCode ) {
            // not equal, return false
            throw new UnauthorizedException("return code assertion failed. expected {$httpCode}, got {$this->response->getStatusCode()}");
        }
    }

    /**
     * @Then scope into the :name property
     */
    public function scopeIntoTheProperty($name)
    {
        // now we scope into our property
        if (!property_exists($this->getCurrentScope(), $name)) {
            // error
            throw new \Exception("Property {$name} does not exist in response body!");
        }

        // just scope
        $this->dataScope = $this->dataScope->{$name};
    }

    /**
     * @Then the properties exist:
     */
    public function thePropertiesExist(PyStringNode $string)
    {
        $propNames = (string) $string;
        $props = explode("\n", $propNames);
        $propCount = count($props);
        echo "Testing {$propCount} properties on current scope...\n";
        echo "===================================================\n";

        // iterate over all props
        foreach ($props as $propName) {
            echo "Checking prop existence: {$propName}...";

            if (!property_exists($this->getCurrentScope(), $propName)) {
                // not found, throw exception
                throw new PendingException("Property {$propName} is not found in current scope");
            }
            echo "FOUND!\n";
        }

        return true;
    }

    /**
     * @Then the :propName property is an integer
     */
    public function thePropertyIsAnInteger($propName)
    {
        // check if the type is integer
        if (is_int($this->getCurrentScope()->{$propName})) {
            return true;
        }
        
        // false, throw exception
        throw new PendingException("Property {$propName} is not an integer");
    }

    /**
     * @Then the :propName property is an array
     */
    public function thePropertyIsAnArray($propName)
    {
        // check if the type is integer
        if (is_array($this->getCurrentScope()->{$propName})) {
            return true;
        }
        
        // false, throw exception
        throw new PendingException("Property {$propName} is not an array");
    }

    /**
     * @Then the :propName property contains at least :count item
     */
    public function thePropertyContainsAtLeastItem($propName, $count)
    {
        // check that property exists
        if (!property_exists($this->getCurrentScope(), $propName)) {
            throw new PendingException("Property {$propName} not found in current scope");
        }

        // check
        $propContentCount = count($this->getCurrentScope()->{$propName});
        if ($propContentCount >= $count) {
            return true;
        }

        // fail
        throw new PendingException("Property {$propName} has < than {$count} items, actually only has {$propContentCount}");
    }

    /**
     * @Then the :arg1 property contains exactly :count item
     */
    public function thePropertyContainsExactlyItem($propName, $count)
    {
        // check that property exists
        if (!property_exists($this->getCurrentScope(), $propName)) {
            throw new PendingException("Property {$propName} not found in current scope");
        }

        // check
        $propContentCount = count($this->getCurrentScope()->{$propName});
        if ($propContentCount == $count) {
            return true;
        }

        throw new PendingException("Property {$propName} doesn't have EXACTLY {$count} items, actually has {$propContentCount}");
    }

    /**
     * @Given I have the payload:
     */
    public function iHaveThePayload(PyStringNode $string)
    {
        // usually a json string, just put into the request directly
        $this->requestOptions['headers']['Content-Type']   = 'application/json';
        $this->requestOptions['body'] = (string) $string;
    }

    /**
     * @Given I use the token :tokString
     */
    public function iUseTheToken($tokString)
    {
        // fill token
        $this->requestOptions['headers']['Authorization'] = "Bearer {$tokString}";

        echo "Request will be supplied with Authorization: Bearer $tokString\n";
    }
}
