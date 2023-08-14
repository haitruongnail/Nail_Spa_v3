<?php

/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Lookups
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */


namespace Twilio\Rest\Lookups\V1;

use Twilio\Exceptions\TwilioException;
use Twilio\InstanceResource;
use Twilio\Options;
use Twilio\Values;
use Twilio\Version;


/**
 * @property array|null $callerName
 * @property string|null $countryCode
 * @property string|null $phoneNumber
 * @property string|null $nationalFormat
 * @property array|null $carrier
 * @property array|null $addOns
 * @property string|null $url
 */
class PhoneNumberInstance extends InstanceResource
{
    /**
     * Initialize the PhoneNumberInstance
     *
     * @param Version $version Version that contains the resource
     * @param mixed[] $payload The response payload
     * @param string $phoneNumber The phone number to lookup in [E.164](https://www.twilio.com/docs/glossary/what-e164) format, which consists of a + followed by the country code and subscriber number.
     */
    public function __construct(Version $version, array $payload, string $phoneNumber = null)
    {
        parent::__construct($version);

        // Marshaled Properties
        $this->properties = [
            'callerName' => Values::array_get($payload, 'caller_name'),
            'countryCode' => Values::array_get($payload, 'country_code'),
            'phoneNumber' => Values::array_get($payload, 'phone_number'),
            'nationalFormat' => Values::array_get($payload, 'national_format'),
            'carrier' => Values::array_get($payload, 'carrier'),
            'addOns' => Values::array_get($payload, 'add_ons'),
            'url' => Values::array_get($payload, 'url'),
        ];

        $this->solution = ['phoneNumber' => $phoneNumber ?: $this->properties['phoneNumber'], ];
    }

    /**
     * Generate an instance context for the instance, the context is capable of
     * performing various actions.  All instance actions are proxied to the context
     *
     * @return PhoneNumberContext Context for this PhoneNumberInstance
     */
    protected function proxy(): PhoneNumberContext
    {
        if (!$this->context) {
            $this->context = new PhoneNumberContext(
                $this->version,
                $this->solution['phoneNumber']
            );
        }

        return $this->context;
    }

    /**
     * Fetch the PhoneNumberInstance
     *
     * @param array|Options $options Optional Arguments
     * @return PhoneNumberInstance Fetched PhoneNumberInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function fetch(array $options = []): PhoneNumberInstance
    {

        return $this->proxy()->fetch($options);
    }

    /**
     * Magic getter to access properties
     *
     * @param string $name Property to access
     * @return mixed The requested property
     * @throws TwilioException For unknown properties
     */
    public function __get(string $name)
    {
        if (\array_key_exists($name, $this->properties)) {
            return $this->properties[$name];
        }

        if (\property_exists($this, '_' . $name)) {
            $method = 'get' . \ucfirst($name);
            return $this->$method();
        }

        throw new TwilioException('Unknown property: ' . $name);
    }

    /**
     * Provide a friendly representation
     *
     * @return string Machine friendly representation
     */
    public function __toString(): string
    {
        $context = [];
        foreach ($this->solution as $key => $value) {
            $context[] = "$key=$value";
        }
        return '[Twilio.Lookups.V1.PhoneNumberInstance ' . \implode(' ', $context) . ']';
    }
}

