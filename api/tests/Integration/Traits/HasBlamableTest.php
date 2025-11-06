<?php

namespace Tests\Integration\Traits;

use App\Models\Asset;
use App\Models\Org;
use App\Models\User;
use App\Traits\HasBlamable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class HasBlamableTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $anotherUser;
    private Org $org;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->anotherUser = User::factory()->create();
        $this->org = Org::factory()->create();
    }

    public function test_trait_automatically_sets_created_by_when_creating_model(): void
    {
        Auth::login($this->user);

        $asset = Asset::create([
            'org_id' => $this->org->id,
            'name' => 'Test Asset',
            'host' => '192.168.1.1',
            'port' => 3306,
            'status' => 'active',
            'dbms' => 'mysql',
        ]);

        $this->assertEquals($this->user->id, $asset->created_by);
        $this->assertEquals($this->user->id, $asset->updated_by);
    }

    public function test_trait_automatically_sets_updated_by_when_updating_model(): void
    {
        Auth::login($this->user);

        $asset = Asset::create([
            'org_id' => $this->org->id,
            'name' => 'Test Asset',
            'host' => '192.168.1.1',
            'port' => 3306,
            'status' => 'active',
            'dbms' => 'mysql',
        ]);

        Auth::login($this->anotherUser);

        $asset->update(['name' => 'Updated Test Asset']);

        $this->assertEquals($this->user->id, $asset->created_by);
        $this->assertEquals($this->anotherUser->id, $asset->updated_by);
    }

    public function test_trait_handles_unauthenticated_user_gracefully(): void
    {
        Auth::logout();

        $asset = Asset::create([
            'org_id' => $this->org->id,
            'name' => 'Test Asset',
            'host' => '192.168.1.1',
            'port' => 3306,
            'status' => 'active',
            'dbms' => 'mysql',
        ]);

        $this->assertNull($asset->created_by);
        $this->assertNull($asset->updated_by);
    }

    public function test_created_by_relationship_returns_correct_user(): void
    {
        Auth::login($this->user);

        $asset = Asset::create([
            'org_id' => $this->org->id,
            'name' => 'Test Asset',
            'host' => '192.168.1.1',
            'port' => 3306,
            'status' => 'active',
            'dbms' => 'mysql',
        ]);

        $this->assertInstanceOf(User::class, $asset->createdBy);
        $this->assertEquals($this->user->id, $asset->createdBy->id);
        $this->assertEquals($this->user->email, $asset->createdBy->email);
    }

    public function test_updated_by_relationship_returns_correct_user(): void
    {
        Auth::login($this->user);

        $asset = Asset::create([
            'org_id' => $this->org->id,
            'name' => 'Test Asset',
            'host' => '192.168.1.1',
            'port' => 3306,
            'status' => 'active',
            'dbms' => 'mysql',
        ]);

        Auth::login($this->anotherUser);

        $asset->update(['name' => 'Updated Test Asset']);

        $this->assertInstanceOf(User::class, $asset->updatedBy);
        $this->assertEquals($this->anotherUser->id, $asset->updatedBy->id);
        $this->assertEquals($this->anotherUser->email, $asset->updatedBy->email);
    }

    public function test_relationships_return_null_when_no_user_set(): void
    {
        Auth::logout();

        $asset = Asset::create([
            'org_id' => $this->org->id,
            'name' => 'Test Asset',
            'host' => '192.168.1.1',
            'port' => 3306,
            'status' => 'active',
            'dbms' => 'mysql',
        ]);

        $this->assertNull($asset->createdBy);
        $this->assertNull($asset->updatedBy);
    }

    public function test_get_created_by_column_returns_default_column_name(): void
    {
        $asset = new Asset;

        $this->assertEquals('created_by', $asset->getCreatedByColumn());
    }

    public function test_get_updated_by_column_returns_default_column_name(): void
    {
        $asset = new Asset;

        $this->assertEquals('updated_by', $asset->getUpdatedByColumn());
    }

    public function test_custom_column_names_are_respected(): void
    {
        $this->createTestTableWithCustomColumns();

        Auth::login($this->user);

        $model = new TestBlamableModelWithCustomColumns;
        $model->name = 'Test Model';
        $model->save();

        $this->assertEquals($this->user->id, $model->author_id);
        $this->assertEquals($this->user->id, $model->editor_id);
        $this->assertEquals('author_id', $model->getCreatedByColumn());
        $this->assertEquals('editor_id', $model->getUpdatedByColumn());
    }

    public function test_custom_column_relationships_work_correctly(): void
    {
        $this->createTestTableWithCustomColumns();

        Auth::login($this->user);

        $model = new TestBlamableModelWithCustomColumns;
        $model->name = 'Test Model';
        $model->save();

        $this->assertInstanceOf(User::class, $model->createdBy);
        $this->assertEquals($this->user->id, $model->createdBy->id);

        $this->assertInstanceOf(User::class, $model->updatedBy);
        $this->assertEquals($this->user->id, $model->updatedBy->id);
    }

    public function test_trait_only_sets_blame_fields_on_model_events(): void
    {
        Auth::login($this->user);

        $asset = new Asset;
        $asset->org_id = $this->org->id;
        $asset->name = 'Test Asset';
        $asset->host = '192.168.1.1';
        $asset->port = 3306;
        $asset->status = 'active';
        $asset->dbms = 'mysql';

        $this->assertNull($asset->created_by);
        $this->assertNull($asset->updated_by);

        $asset->save();

        $this->assertEquals($this->user->id, $asset->created_by);
        $this->assertEquals($this->user->id, $asset->updated_by);
    }

    public function test_manual_assignment_is_overridden_by_trait(): void
    {
        Auth::login($this->user);

        $asset = Asset::create([
            'org_id' => $this->org->id,
            'name' => 'Test Asset',
            'host' => '192.168.1.1',
            'port' => 3306,
            'status' => 'active',
            'dbms' => 'mysql',
            'created_by' => $this->anotherUser->id,
            'updated_by' => $this->anotherUser->id,
        ]);

        $this->assertEquals($this->user->id, $asset->created_by);
        $this->assertEquals($this->user->id, $asset->updated_by);
    }

    public function test_updating_does_not_change_created_by_field(): void
    {
        Auth::login($this->user);

        $asset = Asset::create([
            'org_id' => $this->org->id,
            'name' => 'Test Asset',
            'host' => '192.168.1.1',
            'port' => 3306,
            'status' => 'active',
            'dbms' => 'mysql',
        ]);

        $originalCreatedBy = $asset->created_by;

        Auth::login($this->anotherUser);

        $asset->update(['name' => 'Updated Test Asset']);

        $this->assertEquals($originalCreatedBy, $asset->created_by);
        $this->assertEquals($this->anotherUser->id, $asset->updated_by);
    }

    private function createTestTableWithCustomColumns(): void
    {
        \Illuminate\Support\Facades\Schema::create('test_custom_blamable', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('author_id')->nullable()->constrained('users');
            $table->foreignId('editor_id')->nullable()->constrained('users');
            $table->timestamps();
        });
    }
}

class TestBlamableModelWithCustomColumns extends \Illuminate\Database\Eloquent\Model
{
    use HasBlamable;

    protected $table = 'test_custom_blamable';
    protected $fillable = ['name', 'author_id', 'editor_id'];

    protected $createdByAttribute = 'author_id';
    protected $updatedByAttribute = 'editor_id';
}
