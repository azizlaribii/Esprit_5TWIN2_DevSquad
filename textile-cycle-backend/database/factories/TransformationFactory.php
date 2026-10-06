<?php

namespace Database\Factories;

use App\Models\Transformation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransformationFactory extends Factory
{
    protected $model = Transformation::class;

    public function definition(): array
    {
        $idees = [
            ['Sac tote en jean', 'Sac', ['Fil épais', 'Ciseaux']],
            ['Housse de coussin', 'Coussin', ['Fil', 'Rembourrage']],
            ['Pochette zippée', 'Accessoire', ['Fermeture éclair']],
            ['Tapis en bandes tressées', 'Décoration', ['Bandes de tissu']],
            ['Tablier de cuisine', 'Vêtement', ['Sangle', 'Fil']],
        ];
        [$titre, $type, $materiaux] = $this->faker->randomElement($idees);

        return [
            'user_id'       => User::factory(),
            'depot_id'      => null,
            'titre'         => $titre,
            'type_projet'   => $type,
            'description'   => $this->faker->sentence(14),
            'difficulte'    => $this->faker->randomElement(array_keys(Transformation::DIFFICULTES)),
            'duree_estimee' => $this->faker->randomElement(['45 min', '1 h', '2 h', '3 h']),
            'materiaux'     => $materiaux,
            'genere_par_ia' => $this->faker->boolean(60),
            'statut'        => $this->faker->randomElement(array_keys(Transformation::STATUTS)),
        ];
    }
}
