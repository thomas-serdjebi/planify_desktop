<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeolocationService
{
    private string $googleMapsApiKey;
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient, string $googleMapsApiKey)
    {
        $this->httpClient = $httpClient;
        $this->googleMapsApiKey = $googleMapsApiKey;
    }

    public function getCoordinates(string $address): ?array
    {
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address) . "&components=country:FR&region=fr&language=fr&key=" . $this->googleMapsApiKey;
        dd($url);
        
        $response = $this->httpClient->request('GET', $url);
        $data = $response->toArray();
    
        if (isset($data['results'][0]['geometry']['location'])) {
            $latitude = $data['results'][0]['geometry']['location']['lat'];
            $longitude = $data['results'][0]['geometry']['location']['lng'];
        
            // Initialisation du code postal à null
            $postalCode = null;
        
            // Parcours des address_components pour trouver le code postal
            foreach ($data['results'][0]['address_components'] as $component) {
                if (in_array('postal_code', $component['types'])) {
                    $postalCode = $component['long_name'];
                    break;
                }
            }
        
            return [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'postal_code' => $postalCode,
            ];
        }
        
        return null;
    }

    public function getDistanceBetween(string $origin, string $destination): ?array
    {
        $url = sprintf(
            'https://maps.googleapis.com/maps/api/distancematrix/json?origins=%s&destinations=%s&language=fr&key=%s',
            urlencode($origin),
            urlencode($destination),
            $this->googleMapsApiKey
        );
    
        $response = $this->httpClient->request('GET', $url);
        $data = $response->toArray(false);
    
        if (
            isset($data['rows'][0]['elements'][0]['status']) &&
            $data['rows'][0]['elements'][0]['status'] === 'OK'
        ) {
            return [
                'duration' => $data['rows'][0]['elements'][0]['duration']['value'], // en secondes
                'distance' => $data['rows'][0]['elements'][0]['distance']['value'], // en mètres
            ];
        }
    
        return null;
    }
    
}