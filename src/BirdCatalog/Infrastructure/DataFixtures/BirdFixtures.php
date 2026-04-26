<?php

declare(strict_types=1);

namespace App\BirdCatalog\Infrastructure\DataFixtures;

use App\BirdCatalog\Domain\Entity\Bird;
use App\BirdCatalog\Domain\Entity\BirdCatalogVersion;
use App\BirdCatalog\Domain\Entity\BirdTranslation;
use App\BirdCatalog\Domain\ValueObject\BirdDescription;
use App\BirdCatalog\Domain\ValueObject\BirdId;
use App\BirdCatalog\Domain\ValueObject\BirdTranslationId;
use App\BirdCatalog\Domain\ValueObject\CommonName;
use App\BirdCatalog\Domain\ValueObject\InformationUrl;
use App\BirdCatalog\Domain\ValueObject\LanguageCode;
use App\BirdCatalog\Domain\ValueObject\ScientificName;
use App\Shared\Domain\ValueObject\AudioPath;
use App\Shared\Domain\ValueObject\ImagePath;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class BirdFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $birdsData = [
            [
                'id' => 'e2f9a3c1-4b7d-4f8e-9c2b-1d5a6e8b7c9d',
                'scientificName' => 'Bubo bubo',
                'imagePath' => '/uploads/birds/images/bubo_bubo.webp',
                'audioPath' => '/uploads/birds/audios/bubo_bubo.ogg',
                'infoUrl' => 'https://www.sioc.cat/fitxa/Bubo+bubo',
                'translations' => [
                    [
                        'id' => '4f8d29c0-1b5e-4a6c-9f3d-8e7b1a2c5d4e',
                        'languageCode' => 'ca',
                        'commonName' => 'Duc',
                        'description' => "El rapinyaire nocturn més gran d'Europa. Destaca pels seus ulls taronges i plomes al cap en forma d'orella. És un caçador implacable i territorial típic d'hàbitats rocosos i feréstecs.",
                    ],
                    [
                        'id' => '7a6b5c4d-3e2f-41a0-9b8c-7d6e5f4a3b2c',
                        'languageCode' => 'es',
                        'commonName' => 'Búho real',
                        'description' => "La mayor rapaz nocturna de Europa. Destaca por sus ojos naranjas y plumas en la cabeza en forma de oreja. Es un cazador implacable y territorial típico de hábitats rocosos.",
                    ],
                ]
            ],
            [
                'id' => 'c8a2b1f3-5d9e-4c7a-9b8f-3e1d2c4a5b6d',
                'scientificName' => 'Caprimulgus europaeus',
                'imagePath' => '/uploads/birds/images/caprimulgus_europaeus.webp',
                'audioPath' => '/uploads/birds/audios/caprimulgus_europaeus.ogg',
                'infoUrl' => 'https://www.sioc.cat/fitxa/Caprimulgus+europaeus',
                'translations' => [
                    [
                        'id' => '1d2c3b4a-5e6f-47a8-9b0c-1d2e3f4a5b6c',
                        'languageCode' => 'ca',
                        'commonName' => 'Enganyapastors',
                        'description' => "Au nocturna d'esvelt vol silenciós i plomatge extremadament mimètic. S'alimenta d'insectes al vol i el seu cant nupcial és un trinat rítmic, aspre i sostingut molt característic.",
                    ],
                    [
                        'id' => '9f8e7d6c-5b4a-43f2-1e0d-9c8b7a6f5e4d',
                        'languageCode' => 'es',
                        'commonName' => 'Chotacabras europeo',
                        'description' => "Ave nocturna de ágil vuelo silencioso y plumaje extremadamente mimético. Se alimenta de insectos al vuelo y su canto nupcial es un trino rítmico, áspero y sostenido muy característico.",
                    ],
                ]
            ],
            [
                'id' => 'f1d2c3b4-a5e6-4f7d-8c9b-0e1a2b3c4d5e',
                'scientificName' => 'Strix aluco',
                'imagePath' => '/uploads/birds/images/strix_aluco.webp',
                'audioPath' => '/uploads/birds/audios/strix_aluco.ogg',
                'infoUrl' => 'https://www.sioc.cat/fitxa/Strix+aluco',
                'translations' => [
                    [
                        'id' => '2e3f4a5b-6c7d-48e9-0f1a-2b3c4d5e6f7a',
                        'languageCode' => 'ca',
                        'commonName' => 'Gamarús',
                        'description' => "Mussol forestal de port rabassut i profunds ulls negres. Típic de zones boscoses i parcs urbans madurs. El seu cèlebre cant ululat és el so clàssic i misteriós de les nits de bosc.",
                    ],
                    [
                        'id' => '8a9b0c1d-2e3f-44a5-b6c7-d8e9f0a1b2c3',
                        'languageCode' => 'es',
                        'commonName' => 'Cárabo común',
                        'description' => "Búho forestal de porte rechoncho y profundos ojos negros. Típico de zonas boscosas y parques urbanos maduros. Su célebre canto ululado es el sonido clásico y misterioso de las noches de bosque.",
                    ],
                ]
            ],
            [
                'id' => 'a7b8c9d0-e1f2-4a3b-8c7d-6e5f4a3b2c1d',
                'scientificName' => 'Asio otus',
                'imagePath' => '/uploads/birds/images/asio_otus.webp',
                'audioPath' => '/uploads/birds/audios/asio_otus.ogg',
                'infoUrl' => 'https://www.sioc.cat/fitxa/Asio+otus',
                'translations' => [
                    [
                        'id' => '3f4a5b6c-7d8e-49f0-1a2b-3c4d5e6f7a8b',
                        'languageCode' => 'ca',
                        'commonName' => 'Mussol banyut',
                        'description' => "De port esvelt, llueix uns plomalls llargs que erigeix com si fossin orelles quan s'alarma. Hiverna en grup i sol utilitzar vius abandonats de còrvids per criar en pinedes i boscos.",
                    ],
                    [
                        'id' => 'c7d8e9f0-a1b2-43c4-d5e6-f7a8b9c0d1e2',
                        'languageCode' => 'es',
                        'commonName' => 'Búho chico',
                        'description' => "De porte esbelto, luce unos penachos largos que erige como orejas cuando se alarma. Inverna en grupo y suele utilizar nidos abandonados de córvidos para criar en pinares.",
                    ],
                ]
            ],
            [
                'id' => 'd4e5f6a7-b8c9-4d0e-1f2a-3b4c5d6e7f8a',
                'scientificName' => 'Athene noctua',
                'imagePath' => '/uploads/birds/images/athene_noctua.webp',
                'audioPath' => '/uploads/birds/audios/athene_noctua.ogg',
                'infoUrl' => 'https://www.sioc.cat/fitxa/Athene+noctua',
                'translations' => [
                    [
                        'id' => '4a5b6c7d-8e9f-40a1-b2c3-d4e5f6a7b8c9',
                        'languageCode' => 'ca',
                        'commonName' => 'Mussol comú',
                        'description' => "Rapinyaire petit, arrodonit i d'ulls grocs molt expressius. Estretament lligat a ambients agraris tradicionals. És freqüent veure'l actiu de dia, vigilant des de pals o teulades.",
                    ],
                    [
                        'id' => 'd5e6f7a8-b9c0-41d2-e3f4-a5b6c7d8e9f0',
                        'languageCode' => 'es',
                        'commonName' => 'Mochuelo europeo',
                        'description' => "Rapaz pequeña, redondeada y de ojos amarillos muy expresivos. Estrechamente ligada a ambientes agrarios tradicionales. Es común verla activa de día, vigilando desde postes o tejados.",
                    ],
                ]
            ],
            [
                'id' => 'b1c2d3e4-f5a6-4b7c-8d9e-0f1a2b3c4d5e',
                'scientificName' => 'Tyto alba',
                'imagePath' => '/uploads/birds/images/tyto_alba.webp',
                'audioPath' => '/uploads/birds/audios/tyto_alba.ogg',
                'infoUrl' => 'https://www.sioc.cat/fitxa/Tyto+alba',
                'translations' => [
                    [
                        'id' => '5b6c7d8e-9f0a-41b2-c3d4-e5f6a7b8c9d0',
                        'languageCode' => 'ca',
                        'commonName' => 'Òliba',
                        'description' => "Inconfusible per la seva màgica cara pàl·lida en forma de cor i un vol blanc, fantasmagòric i absolutament silenciós. És una gran aliada de la pagesia pel seu control de rosegadors.",
                    ],
                    [
                        'id' => 'e6f7a8b9-c0d1-42e3-f4a5-b6c7d8e9f0a1',
                        'languageCode' => 'es',
                        'commonName' => 'Lechuza común',
                        'description' => "Inconfundible por su mágica cara pálida en forma de corazón y un vuelo blanco, fantasmagórico y absolutamente silencioso. Es una gran aliada rural por su eficaz control de roedores.",
                    ],
                ]
            ],
            [
                'id' => 'e8f9a0b1-c2d3-4e4f-5a6b-7c8d9e0f1a2b',
                'scientificName' => 'Caprimulgus ruficollis',
                'imagePath' => '/uploads/birds/images/caprimulgus_ruficollis.webp',
                'audioPath' => '/uploads/birds/audios/caprimulgus_ruficollis.ogg',
                'infoUrl' => 'https://www.sioc.cat/fitxa/Caprimulgus+ruficollis',
                'translations' => [
                    [
                        'id' => '6c7d8e9f-0a1b-42c3-d4e5-f6a7b8c9d0e1',
                        'languageCode' => 'ca',
                        'commonName' => 'Siboc',
                        'description' => "Parent proper de l'enganyapastors però més gran i pàl·lid, amb un distintiu collar rovellat. És un au estival termòfila que prefereix els espais arbustius oberts i de clima càlid.",
                    ],
                    [
                        'id' => 'f7a8b9c0-d1e2-43f4-a5b6-c7d8e9f0a1b2',
                        'languageCode' => 'es',
                        'commonName' => 'Chotacabras pardo',
                        'description' => "Pariente del chotacabras europeo pero más grande y pálido, con un distintivo collar oxidado. Es un ave estival termófila que prefiere los espacios arbustivos abiertos y cálidos.",
                    ],
                ]
            ],
            [
                'id' => 'c3d4e5f6-a7b8-4c9d-0e1f-2a3b4c5d6e7f',
                'scientificName' => 'Otus scops',
                'imagePath' => '/uploads/birds/images/otus_scops.webp',
                'audioPath' => '/uploads/birds/audios/otus_scops.ogg',
                'infoUrl' => 'https://www.sioc.cat/fitxa/Otus+scops',
                'translations' => [
                    [
                        'id' => '7d8e9f0a-1b2c-43d4-e5f6-a7b8c9d0e1f2',
                        'languageCode' => 'ca',
                        'commonName' => 'Xot',
                        'description' => "El nostre rapinyaire nocturn més petit. Visitant estival expert en camuflatge entre l'escorça dels arbres. El seu cant és un característic asxiulet aflautat i monòton: 'xiu... xiu...'.",
                    ],
                    [
                        'id' => 'a8b9c0d1-e2f3-44a5-b6c7-d8e9f0a1b2c3',
                        'languageCode' => 'es',
                        'commonName' => 'Autillo europeo',
                        'description' => "Nuestra rapaz nocturna más pequeña. Visitante estival experto en camuflaje entre la corteza de los árboles. Su canto es un característico silbido aflautado y monótono: 'tiu... tiu...'.",
                    ],
                ]
            ]
        ];

        foreach ($birdsData as $data) {
            $bird = Bird::create(
                new BirdId($data['id']),
                new ScientificName($data['scientificName']),
                new ImagePath($data['imagePath']),
                new AudioPath($data['audioPath']),
                new InformationUrl($data['infoUrl'])
            );

            foreach ($data['translations'] as $tData) {
                $translation = new BirdTranslation(
                    new BirdTranslationId($tData['id']),
                    $bird,
                    LanguageCode::from($tData['languageCode']),
                    new CommonName($tData['commonName']),
                    new BirdDescription($tData['description'])
                );

                $bird->addTranslation($translation);
            }


            $manager->persist($bird);
        }

        $manager->persist(BirdCatalogVersion::initialize());

        $manager->flush();
    }
}
