<?php
    
    /*******************************************************************
     * 
     * IMAGE X-RAY
     * allows simple request of Exif data
     * 
     * readable images:    jpg, png, bmp, ico
     * 
     * author     komed3
     * version    1.002
     * date       2020/01/25
     * 
     *******************************************************************/
    
    class imgXRay {
        
        // var @param string $image
        private $image;
        
        // var @param array $xray
        private $xray;
        
        // var @param string $outputFormat
        private $outputFormat = 'array';
        
        // var @param array $allowedFormats
        private $allowedFormats = [
            'array', 'json', 'plain'
        ];
        
        // var @param array $readableImages
        public $readableImages = [
            'jpg', 'jpeg', 'png', 'bmp', 'ico'
        ];
        
        // @param string $file image path or url
        function __construct( string $file ) {
            
            if( !$this->isReadableImage( $file ) ) {
                
                die( 'file is not a readable image' );
                
            } else {
                
                $this->image = $file;
                
                if( !$this->readInfo() ) {
                    
                    die( 'error occurred reading of data' );
                    
                }
                
            }
            
        }
        
        // @return bool
        // @param string $file image path or url
        private function isReadableImage( string $file ) {
            
            $headers = @get_headers( $file );
            
            if( file_exists( $file ) || ( $headers && strpos( $headers[0], '200' ) ) ) {
                
                $fileinfo = pathinfo( $file );
                
                if( in_array(
                    strtolower( trim( $fileinfo['extension'] ) ),
                    $this->readableImages
                ) ) {
                    
                    return true;
                    
                }
                
            }
            
            return false;
            
        }
        
        // @return bool
        private function readInfo() {
            
            $this->xray['info'] = pathinfo( $this->image );
            
            $this->xray['size'] = getimagesize( $this->image );
            
            if( !preg_match( '/^http(.+)/', $this->image ) ) {
                
                $this->xray['exif'] = exif_read_data( $this->image, 'ANY_TAG', false );
                
            }
            
            return true;
            
        }
        
        // @return string|array
        // @param string $prop exif property
        // @param mixed $value exif value of property
        private function output( string $prop, $value ) {
            
            switch( $this->outputFormat ) {
                
                case 'plain':
                    if( is_array( $value ) ) {
                        
                        die( 'array could not by converted to string' );
                        
                    } else {
                        
                        return strval( $value );
                        
                    }
                    break;
                
                case 'json':
                    return json_encode( [ $prop => $value ] );
                    break;
                
                case 'array':
                    # >>>
                default:
                    return [ $prop => $value ];
                    break;
                
            }
            
        }
        
        // @return string|bool
        // @param string $section e.g. info, exif ...
        // @param string $prop exif property
        private function getData( string $section, string $prop ) {
            
            if( array_key_exists(
                $section,
                $this->xray
            ) && array_key_exists(
                $prop,
                $this->xray[ $section ]
            ) ) {
                
                return $this->xray[ $section ][ $prop ];
                
            }
            
            return NULL;
            
        }
        
        // @return bool
        // @param string $prop exif property
        private function isExif( string $prop ) {
            
            return ( $this->getData( 'exif', $prop ) == NULL ? false : true );
            
        }
        
        // @return string|null
        // @param string $prop exif property
        private function getExif( string $prop ) {
            
            return $this->getData( 'exif', $prop );
            
        }
        
        // @return float
        // @param string $divide
        private function divider( string $divide ) {
            
            $divs = explode( '/', trim( $divide ) );
            
            if( count( $divs ) == 2 ) {
                
                return $divs[0] / $divs[1];
                
            }
            
            return $divide;
            
        }
        
        // @return float GPS coordinates as decimal
        // @param array $GPS [grad, min, sec]
        // @param string $dir [N, E, S, W]
        private function GPStoDec( array $GPS, string $dir ) {
            
            return ( ( ( $GPS[2] / 60 ) + $GPS[1] ) / 60 ) + $GPS[0] *
                        ( in_array( strtolower( $dir ), [ 's', 'w' ] ) ? (-1) : 1 );
            
        }
        
        // @return string GPS coordinates
        // @param array $GPS [grad, min, sec]
        // @param string $dir [N, E, S, W]
        private function GPStoGMS( array $GPS, string $dir ) {
            
            return floor( $GPS[0] ) . '° ' .
                   floor( $GPS[1] ) . '′ ' .
                   round( $GPS[2] ) . '″ ' .
                   strtoupper( $dir );
            
        }
        
        // @param string $format output format value
        public function setOutputFormat( string $format ) {
            
            if( in_array(
                strtolower( trim( $format ) ),
                $this->allowedFormats
            ) ) {
                
                $this->outputFormat = strtolower( trim( $format ) );
                
            }
            
        }
        
        // @return mixed
        public function getName() {
            
            return $this->output( 'name', $this->getData( 'info', 'filename' ) );
            
        }
        
        // @return mixed
        public function getType() {
            
            return $this->output( 'type', $this->getData( 'info', 'extension' ) );
            
        }
        
        // @return mixed
        // @param string $mode [width, height, size]
        public function getSize( string $mode = 'size' ) {
            
            return $this->output( $mode, $this->getData( 'size', [
                'width' =>  0,
                'height' => 1,
                'size' =>   3
            ][ $mode ] ) );
            
        }
        
        // @return mixed
        public function getMimeType() {
            
            return $this->output( 'mime', $this->getData( 'size', 'mime' ) );
            
        }
        
        // @return mixed
        public function getBitsDepth() {
            
            return $this->output( 'bits', $this->getData( 'size', 'bits' ) );
            
        }
        
        // @return mixed
        public function getChannels() {
            
            return $this->output( 'channels', $this->getData( 'size', 'channels' ) );
            
        }
        
        // @return mixed
        public function getFileSize() {
            
            return $this->output( 'fileSize', $this->getExif( 'FileSize' ) );
            
        }
        
        // @return mixed
        // @param string $format date format
        public function getFileTime( string $format = 'U' ) {
            
            return $this->output( 'fileDateTime', date( $format, $this->getExif( 'FileDateTime' ) ) );
            
        }
        
        // @return mixed
        public function getExifVersion() {
            
            return $this->output( 'exifVersion', $this->getExif( 'ExifVersion' ) );
            
        }
        
        // @return mixed
        public function getSoftware() {
            
            return $this->output( 'software', $this->getExif( 'Software' ) );
            
        }
        
        // @return mixed
        public function getMake() {
            
            return $this->output( 'make', $this->getExif( 'Make' ) );
            
        }
        
        // @return mixed
        public function getModel() {
            
            return $this->output( 'model', $this->getExif( 'Model' ) );
            
        }
        
        // @return mixed
        public function getDescription() {
            
            return $this->output( 'description', $this->getExif( 'ImageDescription' ) );
            
        }
        
        // @return mixed
        public function getComment() {
            
            return $this->output( 'comment', $this->getExif( 'UserComment' ) );
            
        }
        
        // @return mixed
        public function getExposureTime() {
            
            return $this->output( 'exposureTime', $this->getExif( 'ExposureTime' ) );
            
        }
        
        // @return mixed
        // @param bool $decode
        public function getExposureProgram( bool $decode = false ) {
            
            if( $decode ) {
                
                return $this->output( 'exposureProgram', [
                    0 => 'not defined',
                    1 => 'Manual',
                    2 => 'Normal program',
                    3 => 'Aperture priority',
                    4 => 'Shutter priority',
                    5 => 'Creative program',
                    6 => 'Action program',
                    7 => 'Portrait mode',
                    8 => 'Landscape mode'
                ][ $this->getExif( 'ExposureProgram' ) ] );
                
            }
            
            return $this->output( 'exposureProgram', $this->getExif( 'ExposureProgram' ) );
            
        }
        
        // @return mixed
        // @param bool $divide
        public function getExposureBias( bool $divide = false ) {
            
            if( $divide ) {
                
                return $this->output( 'exposureBias', $this->divider( $this->getExif( 'ExposureBiasValue' ) ) );
                
            }
            
            return $this->output( 'exposureBias', $this->getExif( 'ExposureBiasValue' ) );
            
        }
        
        // @return mixed
        public function getFNumber() {
            
            return $this->output( 'f-number', $this->getExif( 'FNumber' ) );
            
        }
        
        // @return mixed
        public function getISOSpeedRatings() {
            
            return $this->output( 'ISOSpeedRatings', $this->getExif( 'ISOSpeedRatings' ) );
            
        }
        
        // @return mixed
        public function getCompressedBitsPerPixel() {
            
            return $this->output( 'compressedBitsPerPixel', $this->getExif( 'CompressedBitsPerPixel' ) );
            
        }
        
        // @return mixed
        // @param bool $rgb
        public function getColorSpace( bool $rgb = false ) {
            
            if( $rgb ) {
                
                return $this->getExif( 'ColorSpace' ) == 1 ? true : false;
                
            }
            
            return $this->output( 'colorSpace', $this->getExif( 'ColorSpace' ) );
            
        }
        
        // @return mixed
        // @param bool $decode
        public function getMeteringMode( bool $decode = false ) {
            
            if( $decode ) {
                
                return $this->output( 'meteringMode', [
                    0 =>   'Unknown',
                    1 =>   'Average',
                    2 =>   'CenterWeightedAverage',
                    3 =>   'Spot',
                    4 =>   'MultiSpot',
                    5 =>   'Pattern',
                    6 =>   'Partial',
                    255 => 'other'
                ][ $this->getExif( 'MeteringMode' ) ] );
                
            }
            
            return $this->output( 'orientation', $this->getExif( 'Orientation' ) );
            
        }
        
        // @return mixed
        // @param bool $decode
        public function getOrientation( bool $decode = false ) {
            
            if( $decode ) {
                
                return $this->output( 'orientation', [
                    1 => 'top left side',
                    2 => 'top right side',
                    3 => 'bottom right side',
                    4 => 'bottom left side',
                    5 => 'left side top',
                    6 => 'right side top',
                    7 => 'right side bottom',
                    8 => 'left side bottom'
                ][ $this->getExif( 'Orientation' ) ] );
                
            }
            
            return $this->output( 'orientation', $this->getExif( 'Orientation' ) );
            
        }
        
        // @return mixed
        // @param string $key [unit, x, y]
        // @param bool $decode
        public function getResolution( string $key = 'unit', bool $decode = false ) {
            
            switch( $key ) {
                
                case 'x':
                    # >>>
                case 'xResolution':
                    return $this->output( 'xResolution', $this->getExif( 'XResolution' ) );
                    break;
                
                case 'y':
                    # >>>
                case 'yResolution':
                    return $this->output( 'yResolution', $this->getExif( 'YResolution' ) );
                    break;
                
                case 'unit':
                    # >>>
                default:
                    if( $decode ) {
                        
                        return $this->output( 'resolutionUnit', [
                            2 => 'inches',
                            3 => 'centimeters'
                        ][ $this->getExif( 'ResolutionUnit' ) ] );
                        
                    } else {
                        
                        return $this->output( 'resolutionUnit', $this->getExif( 'ResolutionUnit' ) );
                        
                    }
                    break;
                
            }
            
        }
        
        // @return mixed
        // @param bool $decode
        public function getYCbCrPos( bool $decode = false ) {
            
            if( $decode ) {
                
                return $this->output( 'YCbCrPos', [
                    1 => 'centered',
                    2 => 'cosited'
                ][ $this->getExif( 'YCbCrPositioning' ) ] );
                
            }
            
            return $this->output( 'YCbCrPos', $this->getExif( 'YCbCrPositioning' ) );
            
        }
        
        // @return mixed
        // @param bool $divide
        public function getBrightness( bool $divide = false ) {
            
            if( $divide ) {
                
                return $this->output( 'brightness', $this->divider( $this->getExif( 'BrightnessValue' ) ) );
                
            }
            
            return $this->output( 'brightness', $this->getExif( 'BrightnessValue' ) );
            
        }
        
        // @return mixed
        // @param bool $divide
        public function getMaxAperture( bool $divide = false ) {
            
            if( $divide ) {
                
                return $this->output( 'maxAperture', $this->divider( $this->getExif( 'MaxApertureValue' ) ) );
                
            }
            
            return $this->output( 'maxAperture', $this->getExif( 'MaxApertureValue' ) );
            
        }
        
        // @return mixed
        // @param bool $decode
        public function getLightSource( bool $decode = false ) {
            
            if( $decode ) {
                
                return $this->output( 'lightSource', [
                    0 =>   'Unknown',
                    1 =>   'Daylight',
                    2 =>   'Fluorescent',
                    3 =>   'Tungsten (incandescent light)',
                    4 =>   'Flash',
                    9 =>   'Fine weather',
                    10 =>  'Cloudy weather',
                    11 =>  'Shade',
                    12 =>  'Daylight fluorescent (D 5700 - 7100K)',
                    13 =>  'Day white fluorescent (N 4600 - 5400K)',
                    14 =>  'Cool white fluorescent (W 3900 - 4500K)',
                    15 =>  'White fluorescent (WW 3200 - 3700K)',
                    17 =>  'Standard light A',
                    18 =>  'Standard light B',
                    19 =>  'Standard light C',
                    20 =>  'D55',
                    21 =>  'D65',
                    22 =>  'D75',
                    23 =>  'D50',
                    24 =>  'ISO studio tungsten',
                    255 => 'Other light source'
                ][ $this->getExif( 'LightSource' ) ] );
                
            }
            
            return $this->output( 'lightSource', $this->getExif( 'LightSource' ) );
            
        }
        
        // @return mixed
        // @param bool $divide
        public function getFocalLength( bool $divide = false ) {
            
            if( $divide ) {
                
                return $this->output( 'focalLength', $this->divider( $this->getExif( 'FocalLength' ) ) );
                
            }
            
            return $this->output( 'focalLength', $this->getExif( 'FocalLength' ) );
            
        }
        
        // @return mixed
        // @param bool $divide
        public function getFocalLength35( bool $divide = false ) {
            
            if( $divide ) {
                
                return $this->output( 'focalLength35', $this->divider( $this->getExif( 'FocalLengthIn35mmFilm' ) ) );
                
            }
            
            return $this->output( 'focalLength35', $this->getExif( 'FocalLengthIn35mmFilm' ) );
            
        }
        
        // @return mixed
        // @param bool $boolean
        public function getCustomRendered( bool $boolean = false ) {
            
            return $this->output( 'customRendered', ( $boolean ?
                            boolval( $this->getExif( 'CustomRendered' ) ) :
                            $this->getExif( 'CustomRendered' ) ) );
            
        }
        
        // @return mixed
        // @param bool $decode
        public function getExposureMode( bool $decode = false ) {
            
            if( $decode ) {
                
                return $this->output( 'exposureMode', [
                    0 => 'Auto exposure',
                    1 => 'Manual exposure',
                    2 => 'Auto bracket'
                ][ $this->getExif( 'ExposureMode' ) ] );
                
            }
            
            return $this->output( 'exposureMode', $this->getExif( 'ExposureMode' ) );
            
        }
        
        // @return mixed
        // @param bool $decode
        public function getWhiteBalance( bool $decode = false ) {
            
            if( $decode ) {
                
                return $this->output( 'whiteBalance', [
                    0 => 'Auto white balance',
                    1 => 'Manual white balance'
                ][ $this->getExif( 'WhiteBalance' ) ] );
                
            }
            
            return $this->output( 'whiteBalance', $this->getExif( 'WhiteBalance' ) );
            
        }
        
        // @return mixed
        public function getDigitalZoomRatio() {
            
            return $this->output( 'digitalZoomRatio', $this->getExif( 'DigitalZoomRatio' ) );
            
        }
        
        // @return mixed
        // @param bool $decode
        public function getSceneCaptureType( bool $decode = false ) {
            
            if( $decode ) {
                
                return $this->output( 'sceneCaptureType', [
                    0 => 'Standard',
                    1 => 'Landscape',
                    2 => 'Portrait',
                    3 => 'Night scene'
                ][ $this->getExif( 'SceneCaptureType' ) ] );
                
            }
            
            return $this->output( 'sceneCaptureType', $this->getExif( 'SceneCaptureType' ) );
            
        }
        
        // @return mixed
        // @param bool $decode
        public function getContrast( bool $decode = false ) {
            
            if( $decode ) {
                
                return $this->output( 'contrast', [
                    0 => 'Normal',
                    1 => 'Soft',
                    2 => 'Hard'
                ][ $this->getExif( 'Contrast' ) ] );
                
            }
            
            return $this->output( 'contrast', $this->getExif( 'Contrast' ) );
            
        }
        
        // @return mixed
        // @param bool $decode
        public function getSaturation( bool $decode = false ) {
            
            if( $decode ) {
                
                return $this->output( 'saturation', [
                    0 => 'Normal',
                    1 => 'Low saturation',
                    2 => 'High saturation'
                ][ $this->getExif( 'Saturation' ) ] );
                
            }
            
            return $this->output( 'saturation', $this->getExif( 'Saturation' ) );
            
        }
        
        // @return mixed
        // @param bool $decode
        public function getSharpness( bool $decode = false ) {
            
            if( $decode ) {
                
                return $this->output( 'sharpness', [
                    0 => 'Normal',
                    1 => 'Soft',
                    2 => 'Hard'
                ][ $this->getExif( 'Sharpness' ) ] );
                
            }
            
            return $this->output( 'sharpness', $this->getExif( 'Sharpness' ) );
            
        }
        
        // @return mixed
        public function getInterOperabilityIndex() {
            
            return $this->output( 'interOperabilityIndex', $this->getExif( 'InterOperabilityIndex' ) );
            
        }
        
        // @return mixed
        // @param string $mode [all, lat, lon, alt]
        // @param bool $dec output as decimal
        public function getGPS( string $mode = 'all', bool $dec = false ) {
            
            if( $this->isExif( 'GPSLatitudeRef' ) ) {
                
                $GPS = [
                    
                    'lat_ref' =>    $this->getExif( 'GPSLatitudeRef' ),
                    
                    'lat' =>        array_map( function( $lat ) {
                                        
                                        return $this->divider( $lat );
                                        
                                    }, $this->getExif( 'GPSLatitude' ) ),
                    
                    'lon_ref' =>    $this->getExif( 'GPSLongitudeRef' ),
                    
                    'lon' =>        array_map( function( $lon ) {
                                        
                                        return $this->divider( $lon );
                                        
                                    }, $this->getExif( 'GPSLongitude' ) ),
                    
                    'alt_ref' =>    $this->getExif( 'GPSAltitudeRef' ),
                    
                    'alt' =>        $this->divider( $this->getExif( 'GPSAltitude' ) )
                    
                ];
                
                switch( $mode ) {
                    
                    case 'lat':
                        if( $dec ) {
                            
                            return $this->output( 'latitude', $this->GPStoDec( $GPS['lat'], $GPS['lat_ref'] ) );
                            
                        } else {
                            
                            return $this->output( 'latitude', $this->GPStoGMS( $GPS['lat'], $GPS['lat_ref'] ) );
                            
                        }
                        break;
                    
                    case 'lon':
                        if( $dec ) {
                            
                            return $this->output( 'longitude', $this->GPStoDec( $GPS['lon'], $GPS['lon_ref'] ) );
                            
                        } else {
                            
                            return $this->output( 'longitude', $this->GPStoGMS( $GPS['lon'], $GPS['lon_ref'] ) );
                            
                        }
                        break;
                    
                    case 'alt':
                        return $this->output( 'altitude', [
                            'ref' =>    $GPS['alt_ref'],
                            'refR' =>    [
                                '' => 'undefined',
                                0 => 'Above sea level',
                                1 => 'Below sea level'
                            ][ trim( $GPS['alt_ref'] ) ],
                            'alt' =>    $GPS['alt']
                        ] );
                        break;
                    
                    case 'all':
                        # >>>
                    default:
                        return $this->output( 'GPS', $GPS );
                        break;
                    
                }
                
            }
            
            return $this->output( 'GPS', NULL );
            
        }
        
    }
    
?>
