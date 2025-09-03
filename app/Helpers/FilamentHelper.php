<?php

namespace App\Helpers;

class FilamentHelper
{
    /**
     * Get the performance icon based on metric values
     */
    public static function getPerformanceIcon($record)
    {
        $target = $record->kpiMetric->target_value ?? 0;
        $value = $record->value ?? 0;

        if ($target == 0) return 'heroicon-o-minus-circle';

        return ($record->kpiMetric->is_higher_better ?? true)
            ? ($value >= $target ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
            : ($value <= $target ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down');
    }

    /**
     * Get the performance color based on metric values
     */
    public static function getPerformanceColor($record)
    {
        $target = $record->kpiMetric->target_value ?? 0;
        $value = $record->value ?? 0;

        if ($target == 0) return 'gray';

        return ($record->kpiMetric->is_higher_better ?? true)
            ? ($value >= $target ? 'success' : 'danger')
            : ($value <= $target ? 'success' : 'danger');
    }

    /**
     * Calculate performance percentage for a KPI entry detail
     */
    public static function calculatePerformancePercentage($detail)
    {
        $target = $detail->kpiMetric->target_value ?? 0;
        $value = $detail->value ?? 0;

        if ($target == 0) return 0;

        $percentage = ($value / $target) * 100;

        if (!($detail->kpiMetric->is_higher_better ?? true)) {
            // For metrics where lower is better (like error rates)
            if ($value == 0) return 100; // Perfect score
            $percentage = ($target / $value) * 100;
        }

        return $percentage;
    }

    /**
     * Render a 30-day trend chart for a KPI metric
     */
    public static function renderMetricTrend($metricId, $userId)
    {
        // Check if we have valid inputs
        if (empty($metricId) || empty($userId)) {
            return '<div class="text-red-500 italic">No valid trend data available</div>';
        }

        // Placeholder untuk debugging
        return "<div class='text-green-500'>Trend Data for Metric: {$metricId}, User: {$userId}</div>";

        // Placeholder implementation - you would need to implement actual
        // chart rendering logic based on your database structure and charting library

        // Example of placeholder HTML for a simple trend visualization
        $html = '<div class="flex items-center space-x-1 h-6 text-sm text-gray-500 overflow-hidden">';

        // Generate a simple spark line placeholder (you'd replace this with actual data)
        $randomTrend = [];
        for ($i = 0; $i < 30; $i++) {
            $randomTrend[] = rand(50, 100);
        }

        $maxHeight = 20; // Max height of the spark bars in pixels

        // Render mini bar chart
        foreach ($randomTrend as $value) {
            $height = ($value / 100) * $maxHeight;
            $color = $value > 80 ? 'bg-green-500' : ($value > 60 ? 'bg-yellow-500' : 'bg-red-500');
            $html .= '<div class="w-1 bg-gray-200 rounded-sm" style="height: ' . $maxHeight . 'px">';
            $html .= '<div class="' . $color . ' rounded-sm" style="height: ' . $height . 'px; width: 100%; margin-top: auto;"></div>';
            $html .= '</div>';
        }

        $html .= '<span class="ml-2">Placeholder Trend - Replace with actual data</span>';
        $html .= '</div>';

        // Note: In a production environment, you would query your database for the last 30 days of data
        // for this metric and user, then use a proper charting library like Chart.js

        return $html;
    }

    /**
     * Format activity log properties for display
     */
    public static function formatActivityProperties($properties)
    {
        if (empty($properties)) {
            return 'No details available';
        }

        // If properties is a string, try to decode it
        if (is_string($properties)) {
            $properties = json_decode($properties, true);
        }

        // If it's still not an array or is empty, return default message
        if (!is_array($properties) || empty($properties)) {
            return 'No details available';
        }

        // Format the properties into a readable markdown table
        $markdown = "### Changes\n\n";

        // Handle attributes changes
        if (isset($properties['attributes'])) {
            $markdown .= "**New Values:**\n\n";
            $markdown .= "| Field | Value |\n| --- | --- |\n";

            foreach ($properties['attributes'] as $key => $value) {
                // Skip timestamps and IDs
                if (in_array($key, ['id', 'created_at', 'updated_at'])) continue;

                // Format the value
                $formattedValue = is_array($value) ? json_encode($value) : (string) $value;
                $formattedValue = strlen($formattedValue) > 50 ? substr($formattedValue, 0, 50) . '...' : $formattedValue;

                $markdown .= "| " . ucwords(str_replace('_', ' ', $key)) . " | " . $formattedValue . " |\n";
            }
        }

        // Handle old values if available (for updates)
        if (isset($properties['old'])) {
            $markdown .= "\n**Previous Values:**\n\n";
            $markdown .= "| Field | Value |\n| --- | --- |\n";

            foreach ($properties['old'] as $key => $value) {
                // Skip timestamps and IDs
                if (in_array($key, ['id', 'created_at', 'updated_at'])) continue;

                // Format the value
                $formattedValue = is_array($value) ? json_encode($value) : (string) $value;
                $formattedValue = strlen($formattedValue) > 50 ? substr($formattedValue, 0, 50) . '...' : $formattedValue;

                $markdown .= "| " . ucwords(str_replace('_', ' ', $key)) . " | " . $formattedValue . " |\n";
            }
        }

        return $markdown;
    }
}
