<?php

use App\Livewire\Accounting\Settings\JournalTypeIndex;
use App\Models\JournalType;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can render journal types index page', function () {
    Livewire::test(JournalTypeIndex::class)
        ->assertStatus(200)
        ->assertSee('Master Jenis Jurnal');
});

it('can create a new journal type', function () {
    Livewire::test(JournalTypeIndex::class)
        ->set('code', 'TEST')
        ->set('name', 'Jurnal Test Custom')
        ->set('description', 'Keterangan jurnal test')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('journal_types', [
        'code' => 'TEST',
        'name' => 'Jurnal Test Custom',
    ]);
});

it('can update existing journal type', function () {
    $type = JournalType::create([
        'code' => 'KUST',
        'name' => 'Kustom Nama',
        'description' => 'Keterangan awal',
    ]);

    Livewire::test(JournalTypeIndex::class)
        ->call('openEditModal', $type->id)
        ->set('name', 'Kustom Nama Diperbarui')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('journal_types', [
        'id' => $type->id,
        'name' => 'Kustom Nama Diperbarui',
    ]);
});

it('can delete unused journal type', function () {
    $type = JournalType::create([
        'code' => 'DEL',
        'name' => 'Untuk Dihapus',
    ]);

    Livewire::test(JournalTypeIndex::class)
        ->call('delete', $type->id);

    $this->assertDatabaseMissing('journal_types', [
        'id' => $type->id,
    ]);
});
