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
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address) . "&key=" . $this->googleMapsApiKey;

        $response = $this->httpClient->request('GET', $url);
        $data = $response->toArray();

        if (isset($data['results'][0]['geometry']['location'])) {
            return [
                'latitude' => $data['results'][0]['geometry']['location']['lat'],
                'longitude' => $data['results'][0]['geometry']['location']['lng'],
            ];
        }

        return null;
    }
}
