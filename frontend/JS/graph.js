// Supposons que vos données de graphe sont stockées dans une variable `graphData`
var container = document.getElementById('mynetwork');
var data = {
    nodes: new vis.DataSet([
        {id: 1, label: 'Node 1'},
        {id: 2, label: 'Node 2'},
        // Ajoutez vos nœuds ici
    ]),
    edges: new vis.DataSet([
        {from: 1, to: 2},
        // Ajoutez vos arêtes ici
    ])
};
var options = {}; // Options pour personnaliser l'apparence du graphe
var network = new vis.Network(container, data, options);
