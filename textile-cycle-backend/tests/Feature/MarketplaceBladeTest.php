<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ArticleMarketplace;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MarketplaceBladeTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::create([
            'id' => 1,
            'name' => 'Marie Testeur',
            'email' => 'marie.test@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);
    }

    public function test_marketplace_index_page_loads_with_blade_layout(): void
    {
        ArticleMarketplace::create([
            'user_id' => $this->user->id,
            'titre' => 'Veste en Jean Vintage',
            'description' => 'Superbe veste denim recyclée et personnalisée à la main.',
            'categorie' => 'Vestes & Manteaux',
            'marque' => 'Levi\'s',
            'taille' => 'M',
            'genre' => 'Unisexe',
            'etat' => 'Très bon état',
            'type' => 'vente',
            'prix' => 35,
            'statut' => 'disponible',
            'ai_score' => 92,
        ]);

        $response = $this->get('/marketplace');

        $response->assertStatus(200);
        $response->assertSee('Marketplace Circulaire');
        $response->assertSee('Veste en Jean Vintage');
        $response->assertSee('Filtres avancés');
    }

    public function test_marketplace_create_page_renders_form(): void
    {
        $response = $this->get('/marketplace/create');

        $response->assertStatus(200);
        $response->assertSee('Publier un article');
        $response->assertSee('Estimation IA du prix');
    }

    public function test_marketplace_store_creates_article_with_ai(): void
    {
        $payload = [
            'titre' => 'Pantalon Cargo Kaki Urbain',
            'description' => 'Pantalon en toile de coton biologique réparé et remis à neuf.',
            'categorie' => 'Pantalons & Jeans',
            'marque' => 'Zara',
            'taille' => 'L',
            'genre' => 'Homme',
            'etat' => 'Très bon état',
            'type' => 'vente',
            'prix' => 22,
        ];

        $response = $this->actingAs($this->user)->post('/marketplace', $payload);

        $this->assertDatabaseHas('articles_marketplace', [
            'titre' => 'Pantalon Cargo Kaki Urbain',
            'type' => 'vente',
            'statut' => 'disponible',
        ]);

        $article = ArticleMarketplace::where('titre', 'Pantalon Cargo Kaki Urbain')->first();
        $this->assertNotNull($article);
        $this->assertNotNull($article->ai_prix_min);
        $this->assertNotNull($article->ai_prix_max);

        $response->assertRedirect(route('marketplace.show', $article));
    }

    public function test_marketplace_show_page(): void
    {
        $article = ArticleMarketplace::create([
            'user_id' => $this->user->id,
            'titre' => 'Robe d\'été fleurie coton',
            'description' => 'Robe légère seconde main en excellent état pour les beaux jours.',
            'categorie' => 'Robes & Jupes',
            'taille' => 'S',
            'genre' => 'Femme',
            'etat' => 'Très bon état',
            'type' => 'vente',
            'prix' => 18,
            'statut' => 'disponible',
            'ai_score' => 88,
        ]);

        $response = $this->get(route('marketplace.show', $article));

        $response->assertStatus(200);
        $response->assertSee('Robe d\'été fleurie coton');
        $response->assertSee('Faire une demande');
    }

    public function test_marketplace_edit_and_update(): void
    {
        $article = ArticleMarketplace::create([
            'user_id' => $this->user->id,
            'titre' => 'Pull en laine mérinos col V',
            'description' => 'Pull doux et chaud pour l\'hiver, très peu porté.',
            'categorie' => 'Pulls & Sweats',
            'taille' => 'M',
            'genre' => 'Femme',
            'etat' => 'Bon état',
            'type' => 'vente',
            'prix' => 25,
            'statut' => 'disponible',
        ]);

        $response = $this->get(route('marketplace.edit', $article));
        $response->assertStatus(200);
        $response->assertSee('Modifier');

        $updateResponse = $this->actingAs($this->user)->put(route('marketplace.update', $article), [
            'titre' => 'Pull en laine mérinos col V (Mis à jour)',
            'description' => 'Pull doux et chaud pour l\'hiver, très peu porté et lavé.',
            'categorie' => 'Pulls & Sweats',
            'taille' => 'M',
            'genre' => 'Femme',
            'etat' => 'Très bon état',
            'type' => 'vente',
            'prix' => 28,
        ]);

        $updateResponse->assertRedirect(route('marketplace.show', $article));
        $this->assertDatabaseHas('articles_marketplace', [
            'id' => $article->id,
            'titre' => 'Pull en laine mérinos col V (Mis à jour)',
            'prix' => 28,
        ]);
    }

    public function test_marketplace_delete(): void
    {
        $article = ArticleMarketplace::create([
            'user_id' => $this->user->id,
            'titre' => 'T-shirt blanc basique coton',
            'description' => 'T-shirt blanc col rond en coton bio recyclé.',
            'categorie' => 'T-Shirts & Polos',
            'taille' => 'M',
            'genre' => 'Unisexe',
            'etat' => 'Bon état',
            'type' => 'don',
            'statut' => 'disponible',
        ]);

        $response = $this->actingAs($this->user)->delete(route('marketplace.destroy', $article));

        $response->assertRedirect(route('marketplace.index'));
        $this->assertDatabaseMissing('articles_marketplace', [
            'id' => $article->id,
        ]);
    }

    public function test_marketplace_toggle_favori(): void
    {
        $article = ArticleMarketplace::create([
            'user_id' => $this->user->id,
            'titre' => 'Chemise en lin beige',
            'description' => 'Chemise casual chic parfaite pour le printemps.',
            'categorie' => 'Chemises & Blouses',
            'taille' => 'L',
            'genre' => 'Homme',
            'etat' => 'Très bon état',
            'type' => 'echange',
            'statut' => 'disponible',
        ]);

        // Add to favorites
        $response = $this->actingAs($this->user)->post(route('marketplace.toggle-favori', $article));
        $this->assertDatabaseHas('favoris_marketplace', [
            'user_id' => $this->user->id,
            'article_id' => $article->id,
        ]);

        // Remove from favorites
        $response2 = $this->actingAs($this->user)->post(route('marketplace.toggle-favori', $article));
        $this->assertDatabaseMissing('favoris_marketplace', [
            'user_id' => $this->user->id,
            'article_id' => $article->id,
        ]);
    }
}
