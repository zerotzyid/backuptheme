<?php

namespace Pterodactyl\Http\Controllers\Admin\Extensions\darkpsyho;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Pterodactyl\Models\Egg;
use Pterodactyl\Models\Node;
use Pterodactyl\Models\User;
use Pterodactyl\Models\Location;
use Pterodactyl\Models\Objects\DeploymentObject;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Http\Requests\Admin\AdminFormRequest;
use Pterodactyl\Services\Servers\ServerCreationService;
use Pterodactyl\BlueprintFramework\Libraries\ExtensionLibrary\Admin\BlueprintAdminLibrary as BlueprintExtensionLibrary;

/**
 * FastServerController — ZeroTzy Darkpsyho
 * Membuat server secara instan dengan preset RAM 1 GB – 8 GB khusus admin.
 */
class FastServerController extends Controller
{
    /** Preset paket RAM (MB) beserta disk yang mengikuti. */
    public const PRESETS = [
        1 => ['memory' => 1024, 'disk' => 2048,  'cpu' => 50,  'label' => '1 GB', 'color' => '#00f5d4'],
        2 => ['memory' => 2048, 'disk' => 4096,  'cpu' => 100, 'label' => '2 GB', 'color' => '#00bbf9'],
        3 => ['memory' => 3072, 'disk' => 6144,  'cpu' => 150, 'label' => '3 GB', 'color' => '#4cc9f0'],
        4 => ['memory' => 4096, 'disk' => 8192,  'cpu' => 200, 'label' => '4 GB', 'color' => '#9d4edd'],
        5 => ['memory' => 5120, 'disk' => 10240, 'cpu' => 250, 'label' => '5 GB', 'color' => '#7b2cbf'],
        6 => ['memory' => 6144, 'disk' => 12288, 'cpu' => 300, 'label' => '6 GB', 'color' => '#c77dff'],
        7 => ['memory' => 7168, 'disk' => 14336, 'cpu' => 350, 'label' => '7 GB', 'color' => '#f72585'],
        8 => ['memory' => 8192, 'disk' => 16384, 'cpu' => 400, 'label' => '8 GB', 'color' => '#ff0054'],
    ];

    public function __construct(
        private ServerCreationService $creationService,
        private BlueprintExtensionLibrary $blueprint,
    ) {
    }

    /**
     * Tampilkan halaman Fast Create Server.
     */
    public function index(): View
    {
        $eggs = Egg::with('nest')->orderBy('name')->get();
        $users = User::orderBy('name')->get(['id', 'email', 'name', 'root_admin']);
        $nodes = Node::with('location')->get();

        $allocFree = \Pterodactyl\Models\Allocation::query()
            ->whereNull('server_id')
            ->selectRaw('node_id, count(*) as total')
            ->groupBy('node_id')
            ->pluck('total', 'node_id');

        return view('admin.extensions.darkpsyho.fastcreate', [
            'eggs' => $eggs,
            'users' => $users,
            'nodes' => $nodes,
            'locations' => Location::all(),
            'allocFree' => $allocFree,
            'presets' => self::PRESETS,
            'blueprint' => $this->blueprint,
        ]);
    }

    /**
     * Proses pembuatan server instan.
     *
     * @throws \Throwable
     */
    public function store(AdminFormRequest $request): RedirectResponse
    {
        $validated = $request->validate([
            'preset' => 'required|integer|between:1,8',
            'egg_id' => 'required|integer|exists:eggs,id',
            'location_id' => 'nullable|integer|exists:locations,id',
            'user_id' => 'required|integer|exists:users,id',
            'name' => 'required|string|min:1|max:120',
            'quantity' => 'nullable|integer|min:1|max:20',
        ]);

        $preset = self::PRESETS[(int) $validated['preset']];
        $quantity = (int) ($validated['quantity'] ?? 1);
        $egg = Egg::findOrFail($validated['egg_id']);
        $created = [];
        $failed = [];

        for ($i = 0; $i < $quantity; $i++) {
            $name = $quantity > 1
                ? $validated['name'] . ' #' . ($i + 1)
                : $validated['name'];

            $data = [
                'name' => $name,
                'owner_id' => (int) $validated['user_id'],
                'egg_id' => $egg->id,
                'nest_id' => $egg->nest_id,
                'memory' => $preset['memory'],
                'disk' => $preset['disk'],
                'cpu' => $preset['cpu'],
                'swap' => 0,
                'io' => 500,
                'image' => $this->getDefaultImage($egg),
                'startup' => $egg->startup,
                'skip_scripts' => false,
                'database_limit' => 2,
                'allocation_limit' => 0,
                'backup_limit' => 2,
                'oom_disabled' => true,
                'start_on_completion' => false,
            ];

            $deployment = new DeploymentObject();
            if (!empty($validated['location_id'])) {
                $deployment->setLocations([(int) $validated['location_id']]);
            }

            try {
                $server = $this->creationService->handle($data, $deployment);
                $created[] = $server->id;
            } catch (\Throwable $e) {
                $failed[] = $name . ': ' . $e->getMessage();
            }
        }

        if (count($created) > 0) {
            $this->blueprint->alert('success', '✓ ' . count($created) . ' server (' . $preset['label'] . ') berhasil dibuat!');
        }

        if (count($failed) > 0) {
            $this->blueprint->alert('danger', 'Gagal membuat ' . count($failed) . ' server: ' . implode(' | ', array_slice($failed, 0, 3)));
        }

        if (count($created) === 1 && count($failed) === 0) {
            return redirect('/admin/servers/view/' . $created[0]);
        }

        return redirect()->route('admin.extensions.darkpsyho.fastcreate.index');
    }

    /**
     * Ambil docker image default dari egg.
     */
    private function getDefaultImage(Egg $egg): string
    {
        $images = $egg->docker_images;

        if (is_array($images) && count($images) > 0) {
            return (string) array_values($images)[0];
        }

        if (is_string($images) && $images !== '') {
            $decoded = json_decode($images, true);
            if (is_array($decoded) && count($decoded) > 0) {
                return (string) array_values($decoded)[0];
            }
            return $images;
        }

        return 'ghcr.io/pterodactyl/yolks:java_17';
    }
}
