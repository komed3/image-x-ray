# IMAGE X-RAY v. 1.002

__Image X-Ray__ allows easy extraction of exif data from images and photos.

Supported image formats:
* jpg/jpeg
* png
* bmp
* ico

## Usage

### Initialization

```php
$imgxray = new imgXRay( '[PATH OR URL TO IMAGE]' );
```

### Output formats

```php
$imgxray->setOutputFormat( @format );
```

* `@format = plain`: result text only
* `@format = array`: result as php array
* `@format = json`: result as json string

### Functions

* `getName()` – image name
* `getType()` – image type
* `getSize( @mode )` – image sizes
* * `@mode = width`: image width in px
* * `@mode = height`: image height in px
* * `@mode = size`: text string `height="yyy" width="xxx"`
* `getMimeType()` – image mime type
* `getBitsDepth()` – bit/color depth
* `getChannels()` – color channels
* `getFileSize()` – image file size
* `getFileTime( @format )` – image create time
* * `@format`: output format according to php date
* `getExifVersion()` – exif version
* `getSoftware()` – software package(s) used to create the image
* `getMake()` – scanner manufacturer
* `getModel()` – scanner model name or number
* `getDescription()` – user image description
* `getComment()` – keywords or comments on the image
* `getExposureTime()` – exposure time in seconds
* `getExposureProgram( @decode )` – camera exposure program
* * `@decode = true`: return a readable value
* `getExposureBias( @divide )` – exposure bias
* * `@divide = true`: return a float value
* `getExposureMode( @decode )` – exposure mode
* * `@decode = true`: return a readable value
* `getFNumber()` – image F number (`N = f / D`)
* `getISOSpeedRatings()` – ISO Speed and ISO Latitude of the camera
* `getCompressedBitsPerPixel()` – compressed bits per pixel
* `getColorSpace( @rgb )` – color space specifier
* * `@rgb = true`: returns `true` or `false` for RGB color space
* `getMeteringMode( @decode )` – metering mode
* * `@decode = true`: return a readable value
* `getOrientation( @decode )` – orientation of the image with respect to the rows and columns
* * `@decode = true`: return a readable value
* `getResolution( @mode, @decode )` – image resolution
* * `@mode = unit`: resolution unit
* * `@mode = x`: x resolution
* * `@mode = y`: y resolution
* * `@decode = true`: return a readable value
* `getYCbCrPos( @decode )` – positioning of subsampled chrominance components relative to luminance samples
* * `@decode = true`: return a readable value
* `getBrightness( @divide )` – brightness value
* * `@divide = true`: return a float value
* `getMaxAperture( @divide )` – smallest F number of the lens
* * `@divide = true`: return a float value
* `getLightSource( @decode )` – kind of light source
* * `@decode = true`: return a readable value
* `getFocalLength( @divide )` – actual focal length of the lens in mm
* * `@divide = true`: return a float value
* `getFocalLength35( @divide )` – equivalent focal length assuming a 35mm film camera in mm
* * `@divide = true`: return a float value
* `getCustomRendered( @boolean )` – use of special processing on image data
* * `@boolean = true`: return a boolean
* `getWhiteBalance( @decode )` – white balance
* * `@decode = true`: return a readable value
* `getDigitalZoomRatio()` – digital zoom ratio
* `getSceneCaptureType( @decode )` – type of scene
* * `@decode = true`: return a readable value
* `getContrast( @decode )` – image contrast
* * `@decode = true`: return a readable value
* `getSaturation( @decode )` – image saturation
* * `@decode = true`: return a readable value
* `getSharpness( @decode )` – image sharpness
* * `@decode = true`: return a readable value
* `getInterOperabilityIndex()` – identification of the Interoperability rule
* `getGPS( @mode, @dec )` – image GPS data
* * `@mode = all`: all GPS data
* * `@mode = lat`: GPS latitude
* * `@mode = lon`: GPS longitude
* * `@mode = alt`: GPS altitude as array [ reference, readable reference, altitude ]
* * `@dec = true`: return latitude/longitude as float values

## Example

```php
$imgxray = new imgXRay( './test.jpg' );
$imgxray->setOutputFormat( 'plain' );

print 'image name: ' . $imgxray->getName() . PHP_EOL;
print 'image orientation: ' . $imgxray->getOrientation( true ) . PHP_EOL;
print 'image GPS latitude: ' . $imgxray->getGPS( 'lat' );
```

## Licence

[MIT](COPYING)
