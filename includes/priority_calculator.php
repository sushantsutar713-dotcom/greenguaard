<?php
/**
 * GreenGuard — Smart Report Prioritization Engine
 * 
 * Dynamically computes an environmental urgency score (0-100) based on:
 * - Threat severity
 * - Category ecological weight
 * - Community verifications & dispute ratios
 * - Proximity cluster density
 * - Report lifecycle age
 */

class PriorityCalculator {

    public static function calculate(array $report, array $allReports = []): array {
        $score = 0;

        // 1. Severity Base (Max 40 pts)
        $severity = strtoupper($report['severity'] ?? 'MEDIUM');
        switch ($severity) {
            case 'CRITICAL': $score += 40; break;
            case 'HIGH':     $score += 30; break;
            case 'MEDIUM':   $score += 20; break;
            case 'LOW':      $score += 10; break;
            default:         $score += 20; break;
        }

        // 2. Category Ecological Weight (Max 25 pts)
        $category = strtoupper($report['issue_type'] ?? $report['category'] ?? 'OTHER');
        switch ($category) {
            case 'WATER_POLLUTION':
            case 'INDUSTRIAL_POLLUTION':
                $score += 25;
                break;
            case 'TREE_LOSS':
            case 'WASTE_BURNING':
                $score += 20;
                break;
            case 'ILLEGAL_DUMPING':
            case 'PLASTIC_POLLUTION':
                $score += 15;
                break;
            case 'AIR_POLLUTION':
            case 'NOISE_POLLUTION':
                $score += 12;
                break;
            default:
                $score += 10;
                break;
        }

        // 3. Community Confirmation Weight (Max 20 pts)
        $confirmations = (int)($report['community']['confirmations'] ?? 0);
        $disputes = (int)($report['community']['disputes'] ?? 0);
        $netCommunityScore = ($confirmations * 3) - ($disputes * 5);
        $score += max(0, min(20, $netCommunityScore));

        // 4. Proximity / Cluster Density (Max 15 pts)
        if (!empty($allReports) && isset($report['latitude']) && isset($report['longitude'])) {
            $nearbyCount = 0;
            $lat1 = (float)$report['latitude'];
            $lon1 = (float)$report['longitude'];
            $currentId = $report['report_id'] ?? 0;

            foreach ($allReports as $other) {
                if (($other['report_id'] ?? 0) == $currentId) continue;
                if (!isset($other['latitude']) || !isset($other['longitude'])) continue;

                $lat2 = (float)$other['latitude'];
                $lon2 = (float)$other['longitude'];
                
                $distanceKm = self::haversineDistance($lat1, $lon1, $lat2, $lon2);
                if ($distanceKm <= 3.0 && ($other['status'] ?? '') !== 'RESOLVED') {
                    $nearbyCount++;
                }
            }

            $score += min(15, $nearbyCount * 5);
        }

        // Clamp final score to 0 - 100
        $finalScore = max(5, min(100, (int)$score));

        // Determine Level
        if ($finalScore >= 80) {
            $level = 'CRITICAL';
            $badgeColor = '#ef4444';
        } elseif ($finalScore >= 60) {
            $level = 'HIGH';
            $badgeColor = '#f97316';
        } elseif ($finalScore >= 40) {
            $level = 'MEDIUM';
            $badgeColor = '#eab308';
        } else {
            $level = 'LOW';
            $badgeColor = '#3b82f6';
        }

        return [
            'score' => $finalScore,
            'level' => $level,
            'badge_color' => $badgeColor
        ];
    }

    /**
     * Great circle distance in kilometers
     */
    public static function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
