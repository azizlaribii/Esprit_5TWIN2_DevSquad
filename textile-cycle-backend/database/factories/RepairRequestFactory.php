<?php

namespace Database\Factories;

use App\Models\RepairRequest;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory pour l'entité RepairRequest (Demande de réparation)
 *
 * Relations Eloquent exploitées :
 *   - RepairRequest belongsTo User      (N-1)
 *   - RepairRequest belongsTo Workshop  (N-1 : un atelier hasMany demandes)
 */
class RepairRequestFactory extends Factory
{
    protected $model = RepairRequest::class;

    public function definition(): array
    {
        $defects    = ['Trou', 'Déchirure', 'Tache', 'Fermeture cassée', 'Couture défaite', 'Usure'];
        $locations  = ['Manche gauche', 'Col', 'Dos', 'Poche', 'Jambe droite', 'Genou', 'Coude'];
        $severities = ['faible', 'moyenne', 'elevee'];
        $repairs    = [
            ['Recoudre la partie endommagée', 'Renforcer le tissu'],
            ['Remplacer la fermeture éclair', 'Surpiqûre de renfort'],
            ['Raccommodage à l\'aiguille', 'Reprise de couture'],
        ];

        return [
            // ── Relation 1 : appartient à un utilisateur (N-1)
            'user_id'            => User::factory()->roleUser(),

            // ── Relation 2 : appartient à un atelier (N-1 ; Workshop hasMany RepairRequest)
            'workshop_id'        => Workshop::factory(),

            'photo_path'         => 'repairs/placeholder.jpg',
            'defect_type'        => $this->faker->randomElement($defects),
            'location'           => $this->faker->randomElement($locations),
            'severity'           => $this->faker->randomElement($severities),
            'suggested_repairs'  => $this->faker->randomElement($repairs),
            'estimated_cost_min' => $this->faker->randomFloat(2, 5, 20),
            'estimated_cost_max' => $this->faker->randomFloat(2, 25, 80),
            'confidence'         => $this->faker->randomFloat(2, 0.60, 0.99),
            'status'             => $this->faker->randomElement(['en_attente', 'analysee', 'atelier_choisi', 'en_cours']),
        ];
    }

    /** État : demande en attente d'analyse */
    public function enAttente(): static
    {
        return $this->state(fn () => ['status' => 'en_attente']);
    }

    /** État : demande en cours de réparation */
    public function enCours(): static
    {
        return $this->state(fn () => ['status' => 'en_cours']);
    }

    /** État : réparation terminée */
    public function terminee(): static
    {
        return $this->state(fn () => ['status' => 'terminee']);
    }

    /** État : défaut sévère */
    public function severite(string $niveau): static
    {
        return $this->state(fn () => ['severity' => $niveau]);
    }
}
