<?php

namespace App\Services\DellServer;

use App\Models\Order;
use App\Models\Package;
use App\Models\Settings;
use Illuminate\Support\Facades\Http;

class Service
{
    /**
     * Returns the meta data about this Server/Service
     *
     * @return object
     */
    public static function metaData(): object
    {
        return (object)
        [
            'display_name' => 'Dell Server',
            'author' => 'E',
            'version' => '1.0.0',
            'wemx_version' => ['dev', '>=1.8.0'],
        ];
    }

    /**
     * Define the default configuration values required to setup this service
     *
     * @return array
     */
    public static function setConfig(): array
    {
        return [
            [
                "key" => "dellserver::hostname",
                "name" => "Server Hostname",
                "description" => "Hostname of the Dell server",
                "type" => "text",
                "default_value" => "dell.example.com",
                "rules" => ['required'],
            ],
            [
                "key" => "dellserver::api_key",
                "name" => "API Key",
                "description" => "API key for the Dell server",
                "type" => "password",
                "rules" => ['required'],
            ],
        ];
    }

    /**
     * Define the default package configuration values required when creating new packages
     *
     * @return array
     */
    public static function setPackageConfig(Package $package): array
    {
        return [
            [
                "key" => "memory",
                "name" => "Memory in MB",
                "description" => "Allowed memory in MB",
                "type" => "number",
                "default_value" => 32768, // 32GB
                "rules" => ['required'],
            ],
            [
                "key" => "disk_space",
                "name" => "Disk Space in GB",
                "description" => "Allowed disk space in GB",
                "type" => "number",
                "default_value" => 1000, // 1TB
                "rules" => ['required'],
            ],
        ];
    }

    /**
     * Define the default checkout configuration values displayed to the buyer at checkout
     *
     * @return array
     */
    public static function setCheckoutConfig(Package $package): array
    {
        return [
            [
                "key" => "location",
                "name" => "Server Location",
                "description" => "Where do you want us to deploy your server?",
                "type" => "select",
                "options" => [
                    "US" => "United States",
                    "CA" => "Canada",
                    "DE" => "Germany",
                ],
                "default_value" => "US",
                "rules" => ['required'],
            ],
        ];
    }

    /**
     * This function is responsible for creating an instance of the service.
     *
     * @return void
     */
    public function create(array $data = [])
    {
        $package = $this->order->package;
        $user = $this->order->user;
        $order = $this->order;

        $response = Http::post('https://dell.example.com/api/servers/create', [
            'username' => $user->username,
            'memory' => $package->data('memory'),
            'disk_space' => $package->data('disk_space'),
            'location' => $order->option('location'),
        ]);

        if ($response->failed()) {
            // handle failed response
        }

        $order->update(['data' => $response->json()]);
    }

    /**
     * This function is responsible for suspending an instance of the service.
     *
     * @return void
     */
    public function suspend(array $data = [])
    {
        $response = Http::post('https://dell.example.com/api/servers/suspend', [
            'server_id' => $this->order->data('server_id'),
        ]);

        if ($response->failed()) {
            // handle failed response
        }
    }

    /**
     * This function is responsible for unsuspending an instance of the service.
     *
     * @return void
     */
    public function unsuspend(array $data = [])
    {
        $response = Http::post('https://dell.example.com/api/servers/unsuspend', [
            'server_id' => $this->order->data('server_id'),
        ]);

        if ($response->failed()) {
            // handle failed response
        }
    }

    /**
     * This function is responsible for deleting an instance of the service.
     *
     * @return void
     */
    public function terminate(array $data = [])
    {
        $response = Http::post('https://dell.example.com/api/servers/delete', [
            'server_id' => $this->order->data('server_id'),
        ]);

        if ($response->failed()) {
            // handle failed response
        }
    }

    /**
     * This function is responsible for upgrading or downgrading an instance of this service.
     *
     * @return void
     */
    public function upgrade(Package $oldPackage, Package $newPackage)
    {
        $server_id = $this->order->data['id'];
        $response = Http::post("https://dell.example.com/api/servers/{$server_id}/update", [
            'memory' => $newPackage->data('memory'),
            'disk_space' => $newPackage->data('disk_space'),
        ]);
    }

    /**
     * This function is responsible for automatically logging in to the panel.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loginToPanel(Order $order)
    {
        try {
            $response = Http::post('https://dell.example.com/api/v1/users/1/login-token');
            return redirect($response['login_url']);
        } catch (\Exception $error) {
            return redirect()->back()->withError("Something went wrong, please try again later.");
        }
    }

    /**
     * Test API connection
     */
    public static function testConnection()
    {
        try {
            $response = Http::get('https://dell.example.com/api/servers/test-connection');
            if ($response->failed()) {
                throw new \Exception('Failed to connect to Dell API');
            }
        } catch (\Exception $error) {
            return redirect()->back()->withError("Failed to connect to Dell API. <br><br>{$error->getMessage()}");
        }

        return redirect()->back()->withSuccess("Successfully connected with Dell API");
    }

    /**
     * @throw \Exception
     */
    public static function eventCheckout()
    {
        $response = Http::get('https://dell.example.com/nodes/allocations/available');
        if ($response->failed()) {
            throw new \Exception('Could not find a suitable node to deploy your server on');
        }
    }

    /**
     * This method is called when the user navigates to the package view page
     */
    public function eventLoadPackage(Package $package): void
    {
        // Any logic to execute when the package is loaded
    }

    /**
     * Define custom permissions for this service
     *
     * @return array
     */
    public static function permissions(): array
    {
        return [
            'dellserver.server.start' => [
                'description' => 'Permission to start a Dell server from the dashboard',
            ],
        ];
    }

    /**
     * Define buttons shown at order management page
     *
     * @return array
     */
    public static function setServiceButtons(Order $order): array
    {
        return [
            [
                "name" => "Login to Dell Server",
                "color" => "primary",
                "href" => 'https://'. settings('dellserver::hostname'),
                "target" => "_blank",
            ],
        ];
    }

    /**
     * Define sidebar buttons
     *
     * @return array
     */
    public static function setServiceSidebarButtons(Order $order): array
    {
        return [
            [
                "name" => "Server Details",
                "icon" => "<i class='bx bx-server' ></i>",
                "href" => route('dellserver.details.view', $order->id)
            ],
        ];
    }
}
