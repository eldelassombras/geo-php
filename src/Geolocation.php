<?php

namespace eldelassombras\Geolocation;

/**
 * Geolocation
 *
 * Get latitude/longitude or address using Google Maps API
 *
 * @author Jeroen Desloovere <info@jeroendesloovere.be>
 * @author Claudio Sanhueza <claudio.sanhueza.soto@gmail.com>
 * @version 1.0.0
 */
class Geolocation{
    // API URL
    const API_URL = 'maps.googleapis.com/maps/api/geocode/json';

    private $api_key;
    private $https;

    public function __construct($api_key = null, $https = false){
        $this->https = $https;

        if ($api_key) {
            $this->api_key = $api_key;
            $this->https = true;
        }
    }

    /**
     * Do call
     *
     * @return object
     * @param  array  $parameters
     */
    protected function doCall($parameters = array()){
        // verificar si curl esta disponible
        if (!function_exists('curl_init')) {
            // lanzar error
            throw new GeolocationException('This method requires cURL (http://php.net/curl), it seems like the extension isn\'t installed.');
        }

        // definir url
        $url = ($this->https ? 'https://' : 'http://') . self::API_URL . '?';

        // agregue todos los parámetros a la url
        foreach ($parameters as $key => $value) {
			$url .= $key . '=' . urlencode($value) . '&';
		}

		// recortar el último ampersan (&)
        $url = trim($url, '&');

        if ($this->api_key) {
            $url .= '&key=' . $this->api_key;
        }

        // iniciar curl
        $curl = curl_init();

        // establecer opciones de curl
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) {
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		}

		// ejecutar
        $response = curl_exec($curl);

        // buscar errores
        $errorNumber = curl_errno($curl);
        $errorMessage = curl_error($curl);

        // cerrar curl
        curl_close($curl);

        // tenemos errores?
        if ($errorNumber != '') {
			throw new GeolocationException($errorMessage);
		}

		// redefinir la respuesta como json decodificado
        $response = json_decode($response);
		//print_r($response);
		$tipo = $response->results;
		foreach ($tipo as $key => $value) {
			//echo '<pre>';
			//echo $value->types[0];
			//echo '</pre>';
			switch ($value->types[0]){
				case 'street_address':
					return $tipo;
					break;
				case 'premise':
					return $tipo;
					break;
				default:
					return false;
					break;
			}
		}
    }

    /**
     * Obtener dirección usando latitud / longitud
     *
     * @return array(label, components)
     * @param  float        $latitude
     * @param  float        $longitude
     */
    public function getAddress($latitude, $longitude){
		$addressSuggestions = $this->getAddresses($latitude, $longitude);
		//print_r($addressSuggestions);
		
		if($addressSuggestions){
			return $addressSuggestions[0];
		}else{
			return null;
		}
    }

    /**
     * Obtener direcciones posibles usando latitud / longitud
     *
     * @return array(label, street, streetNumber, city, cityLocal, zip, country, countryLabel)
     * @param  float        $latitude
     * @param  float        $longitude
     */
    public function getAddresses($latitude, $longitude){
        // init results
        $addresses = array();

        // define result
        $addressSuggestions = $this->doCall(array(
            'latlng' => $latitude . ',' . $longitude,
            'sensor' => 'false'
        ));
		
        // loop addresses
        foreach ($addressSuggestions as $key => $addressSuggestion) {
            // init address
            $address = array();

            // define label
            $address['label'] = isset($addressSuggestion->formatted_address) ?
                $addressSuggestion->formatted_address : null
            ;

            // define address components by looping all address components
            foreach ($addressSuggestion->address_components as $component) {
                $address['components'][] = array(
                    'long_name' => $component->long_name,
                    'short_name' => $component->short_name,
                    'types' => $component->types
                );
            }

            $addresses[$key] = $address;
        }

        return $addresses;
    }

    /**
     * Obtener coordenadas latitud / longitud
     *
     * @return array  The latitude/longitude coordinates
     * @param  string $street[optional]
     * @param  string $streetNumber[optional]
     * @param  string $city[optional]
     * @param  string $zip[optional]
     * @param  string $country[optional]
     */
    public function getCoordinates(
        $street = null,
        $streetNumber = null,
        $city = null,
        $zip = null,
        $country = null
    ) {
		// iniciar item
        $item = array();

        // agregar calle
        if (!empty($street)) $item[] = $street;

        // agregar el número de la calle
        if (!empty($streetNumber)) $item[] = $streetNumber;

        // agregar ciudad - comuna
        if (!empty($city)) $item[] = $city;

        // agregar zip
        if (!empty($zip)) $item[] = $zip;

        // agregar país
        if (!empty($country)) $item[] = $country;

        // definir value
        $address = implode(' ', $item);
		
		//print_r($address);

        // definir result
        $results = $this->doCall(array(
            'address' => $address,
            'sensor' => 'false'
        ));

        // coordenadas de retorno latitud / longitud
		if($results != FALSE){
			return array(
				'latitude' => array_key_exists(0, $results) ? (float) $results[0]->geometry->location->lat : null,
				'longitude' => array_key_exists(0, $results) ? (float) $results[0]->geometry->location->lng : null
			);			
		}else{
			return false;
		}
//        return array(
//            'latitude'  => array_key_exists(0, $results) ? (float) $results[0]->geometry->location->lat : null,
//            'longitude' => array_key_exists(0, $results) ? (float) $results[0]->geometry->location->lng : null
//        );
    }
}

/**
 * Geolocation Exception
 *
 * @author Jeroen Desloovere <info@jeroendesloovere.be>
 * @author Claudio Sanhueza <claudio.sanhueza.soto@gmail.com>
 */
class GeolocationException extends \Exception {}
