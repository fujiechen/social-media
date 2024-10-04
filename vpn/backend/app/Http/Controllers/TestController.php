<?php

namespace App\Http\Controllers;

use App\Dtos\UserDto;
use App\Mail\CategoryExpiryEmail;
use App\Mail\CompleteOrderEmail;
use App\Mail\CompleteUserPayoutEmail;
use App\Mail\RegistrationVerificationEmail;
use App\Mail\ResetPasswordEmail;
use App\Models\CategoryUser;
use App\Models\Order;
use App\Models\User;
use App\Models\UserPayout;
use App\Services\MediaService;
use App\Services\OpnsenseGatewayService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;


class TestController extends Controller
{

    public function index(Request $requet)
    {
        if (env('APP_ENV') != 'local') {
            exit;
        }

        CategoryUser::find();
    }

    public function testPost(Request $request) {
        if (env('APP_ENV') != 'local') {
            exit;
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = 'uploads/' . $file->getClientOriginalName();
            Storage::disk('s3-public')->put($filename, file_get_contents($file), 'public');

            // Optionally, return the URL of the uploaded file
            $url = Storage::disk('s3-public')->url($filename);
            return $url;
        }

        return 'No file uploaded';
    }

    function downloadM3U8Segments($m3u8_url, $output_folder)
    {
        // Step 1: Fetch the M3U8 content
        $m3u8_content = file_get_contents($m3u8_url);

        // Step 2: Parse the M3U8 file to get media segment URLs
        $segments = array();
        $lines = explode("\n", $m3u8_content);
        foreach ($lines as $line) {
            if (strpos($line, '#EXTINF:') === 0) {
                // Get the duration value from the #EXTINF line (e.g., #EXTINF:2.000)
                $duration = (float)substr($line, 8);
            } elseif (strpos($line, 'http') === 0) {
                // If the line starts with "http," it is the media segment URL
                $segments[] = array('duration' => $duration, 'url' => trim($line));
            }
        }

        // Step 3: Download the media segments
        foreach ($segments as $index => $segment) {
            $output_file = $output_folder . '/segment_' . $index . '.ts'; // Output file name, you can adjust it as needed.

            // Use file_get_contents() or cURL to download the segment
            // Using file_get_contents():
            file_put_contents($output_file, file_get_contents($segment['url']));

            // Using cURL (uncomment this block and comment the file_get_contents() block if preferred):
            /*
            $ch = curl_init($segment['url']);
            $output_handle = fopen($output_file, 'wb');
            curl_setopt($ch, CURLOPT_FILE, $output_handle);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            curl_close($ch);
            fclose($output_handle);
            */

            // Wait for the duration of the segment to simulate real-time playback
            sleep($segment['duration']);
        }

        echo "M3U8 download completed.";
    }

    public function share(Request $request)
    {

        // Load the background image and the white region image
        $qrCodeImage = Image::make('https://fastly.picsum.photos/id/926/200/200.jpg?hmac=86WBSVWkx7lDDb39T_8jZBqAx_mT8cB1Z0lQzpXEH7A');
        $backgroundImage = Image::make('https://s3.ca-central-1.amazonaws.com/varc.public/2023-07-22/image.png');


        // Calculate the height of the extra white region (you can adjust this value as needed)
        $whiteRegionHeight = 300;

        // Create a new image with the extra white region at the bottom
        $compositeImage = Image::canvas($backgroundImage->width(), $backgroundImage->height() + $whiteRegionHeight, '#FFFFFF');

        // Insert the background image into the composite image
        $compositeImage->insert($backgroundImage, 'top');


        // Calculate the position to place the QR code on the bottom center
        $x = ($compositeImage->width() - $qrCodeImage->width()) / 2 + 300;
        $y = $compositeImage->height() - $qrCodeImage->height() - ($whiteRegionHeight / 2) + 100;

        // Insert the QR code into the composite image within the white region
        $compositeImage->insert($qrCodeImage, 'top-left', (int)$x, (int)$y);

        $compositeImage->text('This is a test', 100, (int)$y, function ($font) {
            $font->file(public_path('arial.ttf')); // Replace with the path to your font file
            $font->size(30);
        });

        // Or return it as a response with appropriate Content-Type header:
        return $compositeImage->response('jpg');


    }

}
