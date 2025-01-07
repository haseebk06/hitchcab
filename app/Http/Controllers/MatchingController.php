<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hitchhiker;
use App\Models\Driver;
use App\Models\Ride;

class MatchingController extends Controller
{

    public function matchHitchhikersAndDrivers(Request $request)
    {
        $maxDistance = 5; // Max distance in km
        $maxTimeDifference = 15; // Max time difference in minutes

        // Fetch the latest entry in the rides table
        $latestRide = Ride::latest()->first();

        if (!$latestRide) {
            return response()->json([
                'status' => false,
                'message' => 'No rides available',
            ]);
        }

        $matches = [];

        if ($latestRide->hitchhiker_id) {
            // If the latest entry is a hitchhiker, find matching drivers
            $drivers = Driver::with(['rides' => function ($query) {
                $query->latest();
            }, 'user', 'ratings'])->get();

            foreach ($drivers as $driver) {
                $driverRide = $driver->rides->first();
                if (!$driverRide) continue;

                // Calculate distance and time difference
                $distance = $this->haversineDistance(
                    $latestRide->origin_latitude,
                    $latestRide->origin_longitude,
                    $driverRide->origin_latitude,
                    $driverRide->origin_longitude
                );

                $timeDiff = abs($latestRide->ride_time - $driverRide->ride_time);

                if ($distance <= $maxDistance) {
                    $averageRating = $driver->ratings->avg('rating');

                    $matchScore = $this->calculateMatchScore($distance, $timeDiff, $maxDistance, $maxTimeDifference);

                    $matches[] = [
                        'driver_id' => $driver->id,
                        'driver_name' => $driver->first_name . ' ' . $driver->last_name,
                        'driver_phone' => $driver->user->phone_number,
                        'driver_profile_image_url' => $driver->profile_image_url,
                        'driver_car_image_url' => $driver->car_image_url,
                        'driver_car_seats' => $driver->car_seats,
                        'driver_ride_time' => $driverRide->ride_time,
                        'driver_fare_price' => $driverRide->fare_price,
                        'driver_rating' => $averageRating ?: 0,
                        'distance' => $distance,
                        'time_difference' => $timeDiff,
                        'match_score' => $matchScore,
                    ];
                }
            }
        } elseif ($latestRide->driver_id) {
            // If the latest entry is a driver, find matching hitchhikers
            $hitchhikers = Hitchhiker::with(['rides' => function ($query) {
                $query->latest();
            }, 'user', 'ratings'])->get();

            foreach ($hitchhikers as $hitchhiker) {
                $hitchhikerRide = $hitchhiker->rides->first();
                if (!$hitchhikerRide) continue;

                // Calculate distance and time difference
                $distance = $this->haversineDistance(
                    $latestRide->origin_latitude,
                    $latestRide->origin_longitude,
                    $hitchhikerRide->origin_latitude,
                    $hitchhikerRide->origin_longitude
                );

                $timeDiff = abs($latestRide->ride_time - $hitchhikerRide->ride_time);

                if ($distance <= $maxDistance) {
                    $averageRating = $hitchhiker->ratings->avg('rating');

                    $matchScore = $this->calculateMatchScore($distance, $timeDiff, $maxDistance, $maxTimeDifference);

                    $matches[] = [
                        'hitchhiker_id' => $hitchhiker->id,
                        'hitchhiker_name' => $hitchhiker->first_name . ' ' . $hitchhiker->last_name,
                        'hitchhiker_profile_image_url' => $hitchhiker->profile_image_url,
                        'hitchhiker_phone' => $hitchhiker->user->phone_number,
                        'hitchhiker_rating' => $averageRating ?: 0,
                        'distance' => $distance,
                        'time_difference' => $timeDiff,
                        'match_score' => $matchScore,
                    ];
                }
            }
        }

        usort($matches, function ($a, $b) {
            return $b['match_score'] <=> $a['match_score'];
        });

        return response()->json([
            'status' => true,
            'matches' => $matches,
        ]);
    }


    /**
     * Calculate the Haversine distance between two points in km.
     */
    private function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Earth's radius in km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c; // Distance in km
    }

    /**
     * Calculate the match score based on distance and time difference.
     */
    private function calculateMatchScore($distance, $timeDiff, $maxDistance, $maxTimeDifference)
    {
        $distanceScore = max(0, (1 - ($distance / $maxDistance)) * 50); // 50% weight
        $timeScore = max(0, (1 - ($timeDiff / $maxTimeDifference)) * 30); // 30% weight

        // Add more criteria for scoring if needed

        return $distanceScore + $timeScore; // Total score in percentage
    }
}
