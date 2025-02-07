// Fonction pour mettre à jour les résultats dynamiquement
function updateMatchResults(matchId) {
    const scoreEquipeA = prompt("Entrez le score de l'équipe A:");
    const scoreEquipeB = prompt("Entrez le score de l'équipe B:");

    if (scoreEquipeA !== null && scoreEquipeB !== null) {
        // Appel AJAX pour mettre à jour les scores dans la base de données
        fetch('/update-score', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                matchId: matchId,
                scoreEquipeA: parseInt(scoreEquipeA),
                scoreEquipeB: parseInt(scoreEquipeB)
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mettre à jour l'affichage des scores dans le DOM
                document.querySelector(`#match-${matchId} .team:nth-child(1) .team-score`).textContent = scoreEquipeA;
                document.querySelector(`#match-${matchId} .team:nth-child(2) .team-score`).textContent = scoreEquipeB;

                // Actualiser la section "vainqueur" si nécessaire
                let winnerText = "Match en attente";
                if (data.vainqueur) {
                    winnerText = `🎉 Vainqueur: ${data.vainqueur}`;
                }

                document.querySelector(`#match-${matchId} .winner`).textContent = winnerText;

                alert("Scores mis à jour avec succès !");
            } else {
                alert("Erreur lors de la mise à jour des scores.");
            }
        });
    } else {
        alert("Les scores doivent être remplis !");
    }
}
