<?php

namespace Database\Seeders;

use App\Models\TokenPreset;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TokenPresetSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the token presets.
     */
    public function run(): void
    {
        $presets = [
            ['name' => 'Soldier', 'type' => 'Creature — Soldier', 'power' => 1, 'toughness' => 1, 'description' => 'A brave soldier ready for battle.', 'color' => 'white'],
            ['name' => 'Zombie', 'type' => 'Creature — Zombie', 'power' => 2, 'toughness' => 2, 'description' => 'A reanimated corpse that hungers for flesh.', 'color' => 'black'],
            ['name' => 'Spirit', 'type' => 'Creature — Spirit', 'power' => 1, 'toughness' => 1, 'description' => 'An ethereal being that haunts the living.', 'color' => 'white'],
            ['name' => 'Goblin', 'type' => 'Creature — Goblin', 'power' => 1, 'toughness' => 1, 'description' => 'A mischievous and often dangerous creature.', 'color' => 'red'],
            ['name' => 'Saproling', 'type' => 'Creature — Saproling', 'power' => 1, 'toughness' => 1, 'description' => 'A small plant creature that spreads rapidly.', 'color' => 'green'],
            ['name' => 'Elemental', 'type' => 'Creature — Elemental', 'power' => 3, 'toughness' => 3, 'description' => 'A being composed of elemental forces.', 'color' => 'blue'],
            ['name' => 'Squirrel', 'type' => 'Creature — Squirrel', 'power' => 1, 'toughness' => 1, 'description' => "A small, nimble creature that's often underestimated.", 'color' => 'green'],
            ['name' => 'Bird', 'type' => 'Creature — Bird', 'power' => 1, 'toughness' => 1, 'description' => "A flying creature that soars through the skies.", 'color' => 'blue'],
            ['name' => 'Insect', 'type' => "Creature — Insect", "power" => 1, "toughness" => 1, "description" => "A small but resilient creature that thrives in various environments.", "color" => "green"],
            ["name" => "Wolf", "type" => "Creature — Wolf", "power" => 2, "toughness" => 2, "description" => "A fierce predator that hunts in packs.", "color" => "green"],
            ["name" => "Elf Warrior", "type" => "Creature — Elf Warrior", "power" => 1, "toughness" => 1, "description" => "A skilled fighter from the elven race, known for agility and precision.", "color" => 'green'],
            ["name" => "Angel", "type" => "Creature — Angel", "power" => 4, "toughness" => 4, "description" => "A celestial being of great power and grace, often associated with protection and divine intervention.", "color" => 'white'],
            ["name" => "Demon", "type" => "Creature — Demon", "power" => 5, "toughness" => 5, "description" => "A malevolent entity from the depths of the underworld, often associated with chaos and destruction.", "color" => 'black'],
            ["name" => "Dragon", "type" => "Creature — Dragon", "power" => 6, "toughness" => 6, "description" => 'A legendary creature of immense power, often associated with fire and flight.', 'color' => 'red'],
            ["name" => "Merfolk", "type" => "Creature — Merfolk", 'power' => 2, 'toughness' => 2, 'description' => 'An aquatic humanoid creature, known for its adaptability and cunning in water environments.', 'color' => 'blue'],
            ["name" => "Giant", "type" => "Creature — Giant", "power" => 4, "toughness" => 4, "description" => "A towering humanoid creature of great strength and resilience.", "color" => 'red'],
            ["name" => "Vampire", "type" => "Creature — Vampire", "power" => 3, "toughness" => 3, "description" => "A nocturnal predator that feeds on the life force of others.", "color" => 'black'],
            ["name" => "Werewolf", "type" => "Creature — Werewolf", "power" => 4, "toughness" => 4, "description" => "A cursed being that transforms under the full moon.", "color" => 'green'],
            ["name" => "Spirit Token", "type" => "Creature — Spirit", "power" => 1, "toughness" => 1, 'description' => '', 'color' => 'white'],
            ["name" => "Treasure Token", "type" => "", "power" => 0, "toughness" => 0, 'description' => 'A valuable token representing wealth or resources.', 'color' => ''],
            ["name" => "Food Token", "type" => "", "power" => 0, "toughness" => 0, 'description' => 'A token representing sustenance or nourishment.', 'color' => ''],
            ["name" => "Clue Token", "type" => "", "power" => 0, "toughness" => 0, 'description' => 'A token representing a piece of information or evidence.', 'color' => ''],
            ["name" => "Energy Token", "type" => "", "power" => 0, "toughness" => 0, 'description' => 'A token representing a source of power or energy.', 'color' => ''],
            ["name" => "Poison Counter", "type" => "", "power" => 0, "toughness" => 0, 'description' => 'A token representing a harmful effect or condition.', 'color' => ''],
        ];

        foreach ($presets as $preset) {
            $preset['color'] = is_array($preset['color'])
                ? ($preset['color'][0] ?? '')
                : (string) $preset['color'];

            $preset['description'] = (string) ($preset['description'] ?? '');

            TokenPreset::firstOrCreate(
                ['name' => $preset['name'], 'type' => $preset['type']],
                $preset,
            );
        }
    }
}
