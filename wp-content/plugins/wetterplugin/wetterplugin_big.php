<?php

/*
Plugin Name: Wetter Widget
Description: Ein WordPress-Plugin zur Anzeige von Wetterdaten.
Version: 1.0
Author: Fabian Ernst | Tridem Internet Services GmbH
*/

// Shortcode-Definition
function wetter_widget_shortcode($atts)
{
    // Standardwerte für Attribute
    $atts = shortcode_atts(
        array(
            'lat' => '51.944913',
            'lon' => '7.773314',
            'appID' => 'fb1d66d296ad62e7f0abc64a3a92251b',
        ),
        $atts
    );

    // API-URL
    $url = 'http://api.openweathermap.org/data/2.5/forecast?lat=' . $atts['lat'] . '&lon=' . $atts['lon'] . '&units=metric&appid=' . $atts['appID'];

    // API-Aufruf durchführen
    $response = file_get_contents($url);

    function findIcon($iconCode)
    {
        if (substr($iconCode, -1) === "n") {
            $iconCode = substr_replace($iconCode, "d", -1);
        }
        switch ($iconCode) {
            case '01d':
                $fontAwesomeIcon = '<i class="far fa-sun"></i>';
                break;
            case '02d':
                $fontAwesomeIcon = '<i class="fas fa-cloud-sun"></i>';
                break;
            case '03d':
                $fontAwesomeIcon = '<i class="fas fa-cloud"></i>';
                break;
            case '04d':
                $fontAwesomeIcon = '<i class="fas fa-cloud-meatball"></i>';
                break;
            case '09d':
                $fontAwesomeIcon = '<i class="fas fa-cloud-showers-heavy"></i>';
                break;
            case '10d':
                $fontAwesomeIcon = '<i class="fas fa-cloud-rain"></i>';
                break;
            case '11d':
                $fontAwesomeIcon = '<i class="fas fa-bolt"></i>';
                break;
            case '13d':
                $fontAwesomeIcon = '<i class="far fa-snowflake"></i>';
                break;
            case '50d':
                $fontAwesomeIcon = '<i class="fas fa-smog"></i>';
                break;
            default:
                $fontAwesomeIcon = '<i class="fab fa-searchengin"></i>';
                break;
        }

        return $fontAwesomeIcon;
    }

// Antwort verarbeiten
    if ($response !== false) {
        $data = json_decode($response, true);

        // Überprüfen, ob die Antwort gültige Daten enthält
        if (isset($data['list']) && !empty($data['list'])) {
            $timestamp = $data['list'][0]['dt'];
            $date = date('d.m.Y H:i', $timestamp);

            $ort = $data['city']['name'];
            if ($ort == "Wolbeck") {
                $ort = "Münster Telgte";
            }

            $temperatur = round($data['list'][0]['main']['temp']);
            $iconCode = $data['list'][0]['weather'][0]['icon'];
            $wetterlage = findIcon($iconCode);
            $max_temperatur = round($data['list'][0]['main']['temp_max']);
            $min_temperatur = round($data['list'][0]['main']['temp_min']);
        } else {
            echo 'Fehler: Ungültige Daten erhalten.';
        }
    } else {
        echo 'Fehler: API-Aufruf fehlgeschlagen.';
    }

    // Wetter-Widget HTML erstellen
    $output = '<div class="wetter-widget-card">';
    $output .= '<h2>' . $ort . '</h2>';
    $output .= '<p class="mainData">' . $temperatur . '°C | ' . $wetterlage . '</p>';
    $output .= '<p>Max: ' . $max_temperatur . '°C | Min: ' . $min_temperatur . '°C</p>';
    $output .= '</div>';

    return $output;
}

add_shortcode('wetter_widget_big', 'wetter_widget_shortcode');

// CSS-Datei für das Wetter-Widget hinzufügen
function wetter_widget_styles()
{
    // Der Pfad zur CSS-Datei relativ zur Hauptdatei des Plugins
    $css_file_path = plugin_dir_url(__FILE__) . 'styles.css';

    // Die CSS-Datei enqueued
    wp_enqueue_style('wetter-widget-styles', $css_file_path);
}

add_action('wp_enqueue_scripts', 'wetter_widget_styles');