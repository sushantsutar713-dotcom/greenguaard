<?php
/**
 * GreenGuard — Environmental Hotspot Detection Service
 * 
 * Analyzes report coordinates to automatically identify geographic clusters
 * of high-risk environmental incidents for rapid municipal intervention.
 */

require_once __DIR__ . '/priority_calculator.php';

class HotspotService {

    /**
     * Detect environmental hotspots from a list of reports
     * @param array $reports Array of reports
     * @param float $radiusKm Cluster radius threshold in km (default 5.0 km)
     * @return array List of detected hotspot clusters
     */
    public static function detectHotspots(array $reports, float $radiusKm = 5.0): array {
        $validReports = array_filter($reports, function($r) {
            return !empty($r['latitude']) && !empty($r['longitude']) && ($r['status'] ?? '') !== 'REJECTED';
        });

        if (count($validReports) < 2) {
            return [];
        }

        $clusters = [];
        $visited = [];

        foreach ($validReports as $idx => $report) {
            $id = $report['report_id'] ?? $idx;
            if (isset($visited[$id])) continue;

            $cluster = [$report];
            $visited[$id] = true;

            $lat1 = (float)$report['latitude'];
            $lon1 = (float)$report['longitude'];

            foreach ($validReports as $otherIdx => $other) {
                $otherId = $other['report_id'] ?? $otherIdx;
                if (isset($visited[$otherId])) continue;

                $lat2 = (float)$other['latitude'];
                $lon2 = (float)$other['longitude'];

                $distance = PriorityCalculator::haversineDistance($lat1, $lon1, $lat2, $lon2);
                if ($distance <= $radiusKm) {
                    $cluster[] = $other;
                    $visited[$otherId] = true;
                }
            }

            // A hotspot requires at least 2 reports in proximity
            if (count($cluster) >= 2) {
                $clusters[] = self::summarizeCluster($cluster);
            }
        }

        // Sort hotspots by incident count & average priority
        usort($clusters, fn($a, $b) => ($b['incident_count'] <=> $a['incident_count']));

        return $clusters;
    }

    private static function summarizeCluster(array $cluster): array {
        $count = count($cluster);
        $totalLat = 0;
        $totalLon = 0;
        $categories = [];
        $severities = [];
        $addresses = [];
        $unresolvedCount = 0;

        foreach ($cluster as $r) {
            $totalLat += (float)$r['latitude'];
            $totalLon += (float)$r['longitude'];

            $cat = $r['issue_type'] ?? $r['category'] ?? 'OTHER';
            $categories[$cat] = ($categories[$cat] ?? 0) + 1;

            $sev = strtoupper($r['severity'] ?? 'MEDIUM');
            $severities[$sev] = ($severities[$sev] ?? 0) + 1;

            if (!empty($r['address'])) {
                $addresses[] = $r['address'];
            }

            if (($r['status'] ?? '') !== 'RESOLVED') {
                $unresolvedCount++;
            }
        }

        arsort($categories);
        $topCategory = array_key_first($categories);

        // Derive representative location name
        $centerLat = $totalLat / $count;
        $centerLon = $totalLon / $count;
        $primaryAddress = $addresses[0] ?? "Region ({$centerLat}, {$centerLon})";

        // Determine Risk Level
        if (isset($severities['CRITICAL']) && $severities['CRITICAL'] >= 1 || $unresolvedCount >= 3) {
            $riskLevel = 'CRITICAL';
            $riskBadge = 'badge-critical';
        } elseif (isset($severities['HIGH']) && $severities['HIGH'] >= 2 || $unresolvedCount >= 2) {
            $riskLevel = 'HIGH';
            $riskBadge = 'badge-high';
        } else {
            $riskLevel = 'MEDIUM';
            $riskBadge = 'badge-medium';
        }

        return [
            'hotspot_name' => self::formatLocationName($primaryAddress),
            'incident_count' => $count,
            'unresolved_count' => $unresolvedCount,
            'top_category' => $topCategory,
            'top_category_label' => ucwords(strtolower(str_replace('_', ' ', $topCategory))),
            'risk_level' => $riskLevel,
            'risk_badge' => $riskBadge,
            'center_lat' => round($centerLat, 4),
            'center_lon' => round($centerLon, 4),
            'reports' => $cluster
        ];
    }

    private static function formatLocationName(string $address): string {
        $parts = explode(',', $address);
        return trim($parts[0] . (isset($parts[1]) ? ', ' . trim($parts[1]) : ''));
    }
}
