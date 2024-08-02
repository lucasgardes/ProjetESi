<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Plan de Métro Paris</title>
    <style>
        body {
            background-color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            flex-direction: column;
        }
        canvas {
            border: 1.5px solid black;
        }
        #mousePosition {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 5px;
            border: 1px solid black;
            font-family: Arial, sans-serif;
            font-size: 14px;
            margin-bottom: 10px;
        }
        #controls {
            margin-top: 10px;
        }
        button {
            margin: 0 5px;
        }
    </style>
</head>
<body>
    <div id="mousePosition">Mouse Position: (x, y)</div>
    <canvas id="metroMap" width="1600" height="800"></canvas>
    <div id="controls">
        <button onclick="zoomIn()">Zoom In</button>
        <button onclick="zoomOut()">Zoom Out</button>
        <button onclick="toggleBackground()">Toggle Background</button>
    </div>
    <script>
        var canvas = document.getElementById("metroMap");
        var mousePositionDiv = document.getElementById("mousePosition");
        var ctx = canvas.getContext('2d');

        var zoomLevel = 1;
        var minZoom = 1;
        var maxZoom = 5;
        var zoomIncrement = 0.5;
        var offsetX = 0;
        var offsetY = 0;
        var showBackground = true; // Variable to control background image

        var data = {
                "lines": {
                    "Croix-Baragnon": {
                        "color": "#FFCD00",
                        "stations": [
                            {"name": "LA_DEFENSE", "x": 212, "y": 235},
                            {"name": "ESPLANADE_DE_LA_DEFENSE", "x": 236, "y": 248},
                            {"name": "PONT_DE_NEUILLY", "x": 262, "y": 263},
                            {"name": "LES_SABLONS", "x": 287, "y": 275},
                            {"name": "PORTE_MAILLOT", "x": 369, "y": 317},
                            {"name": "ARGENTINE", "x": 335, "y": 296},
                            {"name": "CHARLES_DE_GAULLE_ETOILE", "x": 401, "y": 333},
                            {"name": "GEORGE_V", "x": 447, "y": 357},
                            {"name": "FRANKLIN_D_ROOSEVELT", "x": 488, "y": 378},
                            {"name": "CHAMPS_ELYSEES_CLEMENCEAU", "x": 519, "y": 392},
                            {"name": "CONCORDE", "x": 546, "y": 400},
                            {"name": "TUILERIES", "x": 607, "y": 415},
                            {"name": "PALAIS_ROYAL_MUSEE_DU_LOUVRE", "x": 671, "y": 415},
                            {"name": "LOUVRE_RIVOLI", "x": 719, "y": 415},
                            {"name": "CHATELET", "x": 831, "y": 415},
                            {"name": "HOTEL_DE_VILLE", "x": 891, "y": 415},
                            {"name": "SAINT_PAUL", "x": 940, "y": 441},
                            {"name": "BASTILLE", "x": 1007, "y": 450},
                            {"name": "GARE_DE_LYON", "x": 1069, "y": 515},
                            {"name": "REUILLY_DIDEROT", "x": 1141, "y": 494},
                            {"name": "NATION", "x": 1231, "y": 468},
                            {"name": "PORTE_DE_VINCENNES", "x": 1290, "y": 477},
                            {"name": "SAINT_MANDE", "x": 1336, "y": 490},
                            {"name": "BERAULT", "x": 1370, "y": 508},
                            {"name": "CHATEAU_DE_VINCENNES", "x": 1404, "y": 525}
                        ]
                    },
                    "Arts": {
                        "color": "#003CA6",
                        "stations": [
                            {"name": "PORTE_DAUPHINE", "x": 275, "y": 337},
                            {"name": "VICTOR_HUGO", "x": 329, "y": 364},
                            {"name": "CHARLES_DE_GAULLE_ETOILE", "x": 401, "y": 333},
                            {"name": "TERNES", "x": 441, "y": 315},
                            {"name": "COURCELLES", "x": 474, "y": 299},
                            {"name": "MONCEAU", "x": 507, "y": 281},
                            {"name": "VILLIERS", "x": 540, "y": 266},
                            {"name": "ROME", "x": 578, "y": 246},
                            {"name": "PLACE_DE_CLICHY", "x": 626, "y": 241},
                            {"name": "BLANCHE", "x": 697, "y": 241},
                            {"name": "PIGALLE", "x": 772, "y": 241},
                            {"name": "ANVERS", "x": 830, "y": 241},
                            {"name": "BARBES_ROCHECHOUART", "x": 898, "y": 241},
                            {"name": "LA_CHAPELLE", "x": 971, "y": 241},
                            {"name": "STALINGRAD", "x": 1058, "y": 247},
                            {"name": "JAURES", "x": 1092, "y": 260},
                            {"name": "COLONEL_FABIEN", "x": 1092, "y": 287},
                            {"name": "BELLEVILLE", "x": 1092, "y": 311},
                            {"name": "COURONNES", "x": 1130, "y": 334},
                            {"name": "MENILMONTANT", "x": 1170, "y": 353},
                            {"name": "PERE_LACHAISE", "x": 1208, "y": 377},
                            {"name": "PHILIPPE_AUGUSTE", "x": 1208, "y": 397},
                            {"name": "ALEXANDRE_DUMAS", "x": 1208, "y": 420},
                            {"name": "AVRON", "x": 1211, "y": 448},
                            {"name": "NATION", "x": 1231, "y": 468}
                        ]
                    },
                    "Pargaminières": {
                        "color": "#837902",
                        "stations": [
                            {"name": "PONT_DE_LEVALLOIS_BECON", "x": 401, "y": 194},
                            {"name": "ANATOLE_FRANCE", "x": 419, "y": 205},
                            {"name": "LOUISE_MICHEL", "x": 435, "y": 214},
                            {"name": "PORTE_DE_CHAMPERRET", "x": 454, "y": 223},
                            {"name": "PEREIRE", "x": 481, "y": 236},
                            {"name": "WAGRAM", "x": 499, "y": 245},
                            {"name": "MALESHERBES", "x": 521, "y": 255},
                            {"name": "VILLIERS", "x": 540, "y": 266},
                            {"name": "EUROPE", "x": 581, "y": 285},
                            {"name": "SAINT_LAZARE", "x": 617, "y": 312},
                            {"name": "HAVRE_CAUMARTIN", "x": 644, "y": 339},
                            {"name": "OPERA", "x": 673, "y": 352},
                            {"name": "QUATRE_SEPTEMBRE", "x": 706, "y": 371},
                            {"name": "BOURSE", "x": 759, "y": 380},
                            {"name": "SENTIER", "x": 813, "y": 380},
                            {"name": "REAUMUR_SEBASTOPOL", "x": 884, "y": 380},
                            {"name": "ARTS_ET_METIERS", "x": 968, "y": 374},
                            {"name": "TEMPLE", "x": 993, "y": 361},
                            {"name": "REPUBLIQUE", "x": 1025, "y": 348},
                            {"name": "PARMENTIER", "x": 1135, "y": 368},
                            {"name": "RUE_SAINT_MAUR", "x": 1167, "y": 375},
                            {"name": "PERE_LACHAISE", "x": 1208, "y": 377},
                            {"name": "GAMBETTA", "x": 1271, "y": 375},
                            {"name": "PORTE_DE_BAGNOLET", "x": 1333, "y": 376},
                            {"name": "GALLIENI", "x": 1377, "y": 376}
                        ]
                    },
                    "Saint-Rome": {
                        "color": "#111111",
                        "stations": [
                            {"name": "GAMBETTA", "x": 1271, "y": 375},
                            {"name": "PELLEPORT", "x": 1308, "y": 349},
                            {"name": "SAINT_FARGEAU", "x": 1338, "y": 323},
                            {"name": "PORTE_DES_LILAS", "x": 1340, "y": 298}
                        ]
                    },
                    "Saint-Antoine du T": {
                        "color": "#000451",
                        "stations": [
                            {"name": "PORTE_DE_CLIGNANCOURT", "x": 813, "y": 177},
                            {"name": "SIMPLON", "x": 838, "y": 190},
                            {"name": "MARCADET_POISSONNIERS", "x": 857, "y": 207},
                            {"name": "CHATEAU_ROUGE", "x": 857, "y": 222},
                            {"name": "BARBES_ROCHECHOUART", "x": 898, "y": 241},
                            {"name": "GARE_DU_NORD", "x": 956, "y": 259},
                            {"name": "GARE_DE_L_EST", "x": 994, "y": 288},
                            {"name": "CHATEAU_D_EAU", "x": 933, "y": 305},
                            {"name": "STRASBOURG_SAINT_DENIS", "x": 918, "y": 339},
                            {"name": "REAUMUR_SEBASTOPOL", "x": 884, "y": 380},
                            {"name": "ETIENNE_MARCEL", "x": 831, "y": 388},
                            {"name": "LES_HALLES", "x": 830, "y": 401},
                            {"name": "CHATELET", "x": 831, "y": 415},
                            {"name": "CITE", "x": 807, "y": 438},
                            {"name": "SAINT_MICHEL_NOTRE_DAME", "x": 787, "y": 465},
                            {"name": "ODEON", "x": 775, "y": 481},
                            {"name": "SAINT_GERMAIN_DES_PRES", "x": 713, "y": 478},
                            {"name": "SAINT_SUPLICE", "x": 697, "y": 497},
                            {"name": "SAINT_PLACIDE", "x": 697, "y": 523},
                            {"name": "MONTPARNASSE_BIENVENUE", "x": 696, "y": 545},
                            {"name": "VAVIN", "x": 742, "y": 550},
                            {"name": "RASPAIL", "x": 776, "y": 559},
                            {"name": "DENFERT_ROCHEREAU", "x": 806, "y": 577},
                            {"name": "MOUTON_DUVERNET", "x": 791, "y": 596},
                            {"name": "ALESIA", "x": 774, "y": 623},
                            {"name": "PORTE_D_ORLEANS", "x": 755, "y": 633},
                        ]
                    },
                }
            };

        var backgroundImage = new Image();
        backgroundImage.src = "Plan-Metro.1669996027.webp";

        backgroundImage.onload = function() {
            drawMap();
        };

        function drawStation(x, y, text) {
            ctx.beginPath();
            ctx.arc(x, y, 4, 0, 2 * Math.PI, false);
            ctx.fillStyle = 'white';
            ctx.fill();

            ctx.lineWidth = 2;
            ctx.strokeStyle = 'black';
            ctx.stroke();
            if (zoomLevel >= 2) {
                ctx.fillStyle = 'black';
                ctx.font = '9px Arial';
                ctx.textAlign = 'center';
                ctx.fillText(text, x, y + 20);
            }
        }

        function drawLine(x1, y1, x2, y2, color) {
            ctx.beginPath();
            ctx.moveTo(x1, y1);
            ctx.lineTo(x2, y2);
            ctx.strokeStyle = color;
            ctx.lineWidth = 4;
            ctx.stroke();
        }

        function drawMap() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.save();
            ctx.scale(zoomLevel, zoomLevel);
            ctx.translate(offsetX / zoomLevel, offsetY / zoomLevel);
            
            if (showBackground) {
                ctx.drawImage(backgroundImage, 0, 0, canvas.width, canvas.height);
            }

            Object.entries(data.lines).forEach(([lineName, line]) => {
                for (let i = 0; i < line.stations.length - 1; i++) {
                    var station1 = line.stations[i];
                    var station2 = line.stations[i + 1];
                    drawLine(station1.x, station1.y, station2.x, station2.y, line.color);
                    drawStation(station1.x, station1.y, station1.name);
                }
                var lastStation = line.stations[line.stations.length - 1];
                drawStation(lastStation.x, lastStation.y, lastStation.name);
            });

            ctx.restore();
        }

        function zoomIn() {
            if (zoomLevel < maxZoom) {
                zoomLevel += zoomIncrement;
                drawMap();
            }
        }

        function zoomOut() {
            if (zoomLevel > minZoom) {
                zoomLevel -= zoomIncrement;
                drawMap();
            }
        }

        function toggleBackground() {
            showBackground = !showBackground;
            drawMap();
        }

        canvas.addEventListener('mousemove', function(event) {
            var rect = canvas.getBoundingClientRect();
            var x = event.clientX - rect.left;
            var y = event.clientY - rect.top;
            mousePositionDiv.innerHTML = 'Mouse Position: x= ' + Math.round(x) + ' y= ' + Math.round(y);
        });

        canvas.addEventListener('mousedown', function(event) {
            var rect = canvas.getBoundingClientRect();
            startX = event.clientX - rect.left;
            startY = event.clientY - rect.top;
            isPanning = true;
        });

        canvas.addEventListener('mouseup', function() {
            isPanning = false;
        });

        canvas.addEventListener('mousemove', function(event) {
            if (isPanning) {
                var rect = canvas.getBoundingClientRect();
                var x = event.clientX - rect.left;
                var y = event.clientY - rect.top;
                offsetX += (x - startX) / zoomLevel;
                offsetY += (y - startY) / zoomLevel;
                startX = x;
                startY = y;
                drawMap();
            }
        });

        canvas.addEventListener('dblclick', function() {
            zoomIn();
        });
    </script>
</body>
</html>
