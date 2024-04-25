<?php

$rueCroixBaragnon = [
    "La Défense" => ["Esplanade de la Défense"],
    "Esplanade de la Défense" => ["La Défense", "Pont de Neuilly"],
    "Pont de Neuilly" => ["Esplanade de la Défense", "Les Sablons"],
    "Les Sablons" => ["Pont de Neuilly", "Porte Maillot"],
    "Porte Maillot" => ["Les Sablons", "Argentine"],
    "Argentine" => ["Porte Maillot", "Charles de Gaulle-Étoile"],
    "Charles de Gaulle-Étoile" => ["Argentine", "George V"],
    "George V" => ["Charles de Gaulle-Étoile", "Franklin D. Roosevelt"],
    "Franklin D. Roosevelt" => ["George V", "Champs-Élysées-Clemenceau"],
    "Champs-Élysées-Clemenceau" => ["Franklin D. Roosevelt", "Concorde"],
    "Concorde" => ["Champs-Élysées-Clemenceau", "Tuileries"],
    "Tuileries" => ["Concorde", "Palais Royal-Musée du Louvre"],
    "Palais Royal-Musée du Louvre" => ["Tuileries", "Louvre-Rivoli"],
    "Louvre-Rivoli" => ["Palais Royal-Musée du Louvre", "Châtelet"],
    "Châtelet" => ["Louvre-Rivoli", "Hôtel de Ville"],
    "Hôtel de Ville" => ["Châtelet", "Saint-Paul"],
    "Saint-Paul" => ["Hôtel de Ville", "Bastille"],
    "Bastille" => ["Saint-Paul", "Gare de Lyon"],
    "Gare de Lyon" => ["Bastille", "Reuilly-Diderot"],
    "Reuilly-Diderot" => ["Gare de Lyon", "Nation"],
    "Nation" => ["Reuilly-Diderot", "Porte de Vincennes"],
    "Porte de Vincennes" => ["Nation", "Saint-Mandé"],
    "Saint-Mandé" => ["Porte de Vincennes", "Bérault"],
    "Bérault" => ["Saint-Mandé", "Château de Vincennes"],
    "Château de Vincennes" => ["Bérault"]
];

$rueMerlane = [
    "La Défense" => ["Charles de Gaulle-Étoile"],
    "Charles de Gaulle-Étoile" => ["La Défense", "Auber"],
    "Auber" => ["Charles de Gaulle-Étoile", "Châtelet-Les Halles"],
    "Châtelet-Les Halles" => ["Auber", "Gare de Lyon"],
    "Gare de Lyon" => ["Châtelet-Les Halles", "Nation"],
    "Nation" => ["Gare de Lyon"]
];

$graph = array_merge($rueCroixBaragnon, $rueMerlane);
?>
