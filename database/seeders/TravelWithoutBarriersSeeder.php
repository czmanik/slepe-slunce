<?php

namespace Database\Seeders;

use App\Enums\PostStatus;
use App\Models\Author;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class TravelWithoutBarriersSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->first();

        if (! $user) {
            return;
        }

        $author = Author::firstOrCreate(
            ['name' => 'Slepé slunce'],
            ['bio' => 'Praktické zkušenosti s asistovaným cestováním.', 'is_expedition_member' => false, 'sort_order' => 20],
        );

        foreach ($this->articles() as $offset => $article) {
            $post = Post::updateOrCreate(
                ['slug' => $article['slug']],
                [
                    'created_by' => $user->id,
                    'category' => Post::CATEGORY_TRAVEL,
                    'guide_topic' => $article['guide_topic'],
                    'title' => $article['title'],
                    'excerpt' => $article['excerpt'],
                    'body' => $article['body'],
                    'status' => PostStatus::Published,
                    'published_at' => Carbon::parse('2026-09-01 09:00:00')->addMinutes($offset),
                    'seo_title' => $article['title'].' | Slepé slunce',
                    'seo_description' => $article['excerpt'],
                ],
            );

            $post->authors()->syncWithoutDetaching([$author->id => ['sort_order' => 1]]);
        }
    }

    /** @return list<array{slug:string,guide_topic:string,title:string,excerpt:string,body:string}> */
    private function articles(): array
    {
        return [
            [
                'slug' => 'prava-nevidomeho-cestujiciho',
                'guide_topic' => 'pred-cestou',
                'title' => 'Cestovat můžete a máte právo chtít pomoc, která dává smysl',
                'excerpt' => 'Nevidomý člověk nemá cestovat navzdory svému postižení. Má cestovat s informacemi, respektem a pomocí, kterou opravdu potřebuje.',
                'body' => '<p>Evropská pravidla chrání lidi se zdravotním postižením a s omezenou schopností pohybu a orientace při cestování vlakem, dálkovým autobusem i letadlem. Základ je jednoduchý: cestující nesmí být odmítnut jen kvůli postižení, má dostat přístupné informace a v řadě situací bezplatnou asistenci.</p><p>Asistence dopravce není totéž co vlastní parťák. Dopravce řeší cestu a pohyb v jeho systému. Parťák může být oporou mimo něj - při hledání ubytování, večeři nebo během delšího pobytu.</p><h2>Jak může pomoci Slepé slunce</h2><p>Pomůžeme vybrat spoj, rozložit přestupy, objednat asistenci a připravit plán B. Nejde o prodej cizího nároku, ale o lidskou službu, která spojí cestujícího, jeho parťáka a dopravce do jednoho srozumitelného plánu.</p><h2>Před cestou si ověřte</h2><ul><li>Kdo provozuje každý úsek cesty</li><li>Kde a jak se objednává asistence</li><li>Co přesně potřebujete, aby vám dopravce pomohl zvládnout</li></ul><p><strong>Zdroj:</strong> <a href="https://europa.eu/youreurope/citizens/travel/transport-disability/reduced-mobility/index_cs.htm">Your Europe</a></p>',
            ],
            [
                'slug' => 'dalkovy-autobus-asistence',
                'guide_topic' => 'doprava',
                'title' => 'Dálkový autobus: dobrá cesta začíná 36 hodin před odjezdem',
                'excerpt' => 'Autobus může být přímý a pohodlný. Je ale potřeba vědět, odkud se odjíždí, kdo zajišťuje asistenci a kdy být na místě.',
                'body' => '<p>U pravidelných dálkových autobusových a autokarových linek v EU se zvláštní práva týkají spojů, jejichž plánovaná trasa měří alespoň 250 kilometrů. Cestující má mít nárok na bezplatnou pomoc v určených terminálech i při nástupu a výstupu.</p><p>Pomoc je potřeba oznámit nejméně 36 hodin předem dopravci, prodejci jízdenky nebo cestovní kanceláři. Terminál může požádat, abyste přišli na určené místo až hodinu před odjezdem.</p><h2>Před cestou si ověřte</h2><ul><li>Je asistence potvrzena písemně?</li><li>Znáte přesné místo setkání a čas příchodu?</li><li>Máte kontakt na dopravce i číslo rezervace?</li></ul><p><strong>Zdroj:</strong> <a href="https://europa.eu/youreurope/citizens/travel/transport-disability/reduced-mobility/index_cs.htm">Your Europe</a></p>',
            ],
            [
                'slug' => 'vlak-a-asistence',
                'guide_topic' => 'doprava',
                'title' => 'Vlak bez zbytečných hádanek: asistence od dveří nádraží až k vozu',
                'excerpt' => 'Neznámé nádraží umí být nepřehledné. Asistence je nástroj, jak věnovat energii cestě, ne hledání správného nástupiště.',
                'body' => '<p>Evropské pravidlo pro železnici počítá s tím, že potřebu asistence oznámíte nejméně 24 hodin před cestou. Pokud lhůtu nestihnete, dopravce nebo správce nádraží má stále vyvinout přiměřené úsilí, aby vám umožnil cestovat podle plánu.</p><p>České dráhy uvádějí pro vnitrostátní cesty objednání nejméně 24 hodin před odjezdem; u cest do nebo ze zahraničí žádají kontakt nejpozději 36 hodin před odjezdem. Asistence může zahrnovat orientaci na neznámém nádraží, doprovod k vlaku a pomoc při nástupu nebo výstupu.</p><h2>Před cestou si ověřte</h2><ul><li>Kdo provozuje každý vlak</li><li>Kolik času máte na přestup</li><li>Co uděláte při zpoždění prvního vlaku</li></ul><p><strong>Zdroj:</strong> <a href="https://www.cd.cz/cestovani-zdravotne-hendikepovanych/-29453/">České dráhy</a></p>',
            ],
            [
                'slug' => 'letadlo-a-asistence',
                'guide_topic' => 'doprava',
                'title' => 'Letadlo: asistence má začít už při rezervaci',
                'excerpt' => 'Letiště nemusí být labyrint. Správně objednaná asistence provede cestujícího od dohodnutého místa až k letadlu.',
                'body' => '<p>Při letecké cestě je dobré nahlásit potřebu asistence už při nákupu letenky. Evropská pravidla pracují s lhůtou nejméně 48 hodin před plánovaným odletem.</p><p>Objednejte pomoc u aerolinky, u které letíte, a uveďte, že jste nevidomý či slabozraký cestující. Popište, kterou část průchodu letištěm potřebujete pokrýt. Nezapomeňte zmínit vodicího psa, přestup nebo pomoc po příletu.</p><p>Na Letišti Praha asistenční služba zahrnuje pomoc při odbavení, bezpečnostní kontrole i přesunu po letišti při odletu a příletu.</p><p><strong>Zdroje:</strong> <a href="https://eur-lex.europa.eu/legal-content/EN/TXT/?uri=celex:32006R1107">EUR-Lex</a>, <a href="https://www.prg.aero/asistencni-sluzba">Letiště Praha</a></p>',
            ],
            [
                'slug' => 'jak-objednat-asistenci',
                'guide_topic' => 'pred-cestou',
                'title' => 'Jak si objednat asistenci, aby o vás opravdu věděli',
                'excerpt' => 'Dobrá žádost o asistenci není dlouhá. Je konkrétní, potvrzená a říká přesně to, co potřebujete.',
                'body' => '<p>Není nutné popisovat celý zdravotní příběh. Dopravci potřebujete předat praktickou informaci: kdo cestuje, na kterém spoji, kde se setkáte a jaká pomoc je potřeba.</p><blockquote>Dobrý den, na spoj [číslo] dne [datum] žádám asistenci pro nevidomého cestujícího. Potřebuji doprovod od [místo setkání] k [vlaku, autobusu nebo gatu] a potvrzení místa a času setkání.</blockquote><h2>Když cestujete s parťákem</h2><p>Oba mají mít uložené telefonní číslo toho druhého a domluvené místo pro opětovné setkání. V rušném prostoru se můžete rozdělit: cestující čeká na bezpečném, jasně pojmenovaném místě a parťák mezitím vyřídí informaci, jízdenku nebo změnu u personálu. Neodchází se beze slova ani s neurčitým „hned jsem zpátky“.</p>',
            ],
            [
                'slug' => 'kdyz-asistence-neprijde',
                'guide_topic' => 'pred-cestou',
                'title' => 'Když asistence nepřijde: nejdřív cesta, potom stížnost',
                'excerpt' => 'Selhání asistence není vaše vina. V první chvíli potřebujete najít řešení, ne vyhrát právní spor.',
                'body' => '<p>Začněte klidně a konkrétně: „Mám objednanou asistenci na spoj číslo…, čekám na určeném místě od…, potřebuji se dostat k…“ Oslovte pracovníka dopravce, informační službu, vedoucího směny nebo řidiče.</p><p>Pokud cestujete s parťákem, rozdělte úkoly: jeden zůstává s cestujícím, druhý hledá pomoc. Cestující ví, kde čeká, jak dlouho přibližně a jak se parťák ozve. Parťák se vrací přesně na dohodnuté místo nebo nejdřív volá.</p><p>Zapište si čas, místo, číslo spoje a to, co se stalo. Stížnost pak napište věcně dopravci.</p><p><strong>Zdroj:</strong> <a href="https://europa.eu/youreurope/citizens/travel/passenger-rights/bus-and-coach/index_en.htm">Your Europe</a></p>',
            ],
            [
                'slug' => 'nekolikadennipobyt-s-partakem',
                'guide_topic' => 's-partakem',
                'title' => 'Dovolená začíná po příjezdu: jak se připravit na několik dní spolu',
                'excerpt' => 'Aby byla dovolená opravdu dovolenou, potřebují cestující i parťáci prostor plánovat, odpočívat a říct si včas, co nefunguje.',
                'body' => '<p>U vícedenního pobytu se vyplatí připravit víc než jízdenku. Je potřeba probrat přání, tempo, zdravotní potřeby, jídlo, soukromí, rozpočet a možnosti odpočinku. Nevidomý člověk má mít skutečný vliv na to, kam půjde, co zažije a kdy si řekne o změnu.</p><p>Slepé slunce může pomoci sestavit trasu, najít přístupné varianty, objednat asistenci dopravce, domluvit ubytování a připravit dvojici cestující–parťák na konkrétní situace.</p><p>U snídaně se domlouvá den, večer se bez spěchu řekne, co bylo skvělé, co unavilo a co chce každý změnit. Parťáci se střídají; nikdo nemá fungovat nepřetržitě.</p>',
            ],
            [
                'slug' => 'vodici-pes-na-cestach',
                'guide_topic' => 's-partakem',
                'title' => 'Vodicí pes na cestách: parťák se čtyřmi tlapkami má vlastní pravidla',
                'excerpt' => 'Vodicí pes není zavazadlo ani maskot výpravy. Je to pracovní partner svého člověka.',
                'body' => '<p>Před cestou se vždy ptejte konkrétního dopravce na podmínky přepravy vodicího psa a u zahraniční cesty také na podmínky cílové země. Pravidla pro doklady, očkování nebo vstup se mohou lišit.</p><p>Na psa nesahejte bez souhlasu, nevolejte na něj, nekrmte ho a neberte ho za postroj. O jeho prostoru, odpočinku a potřebách rozhoduje především jeho člověk.</p><p>Při delší cestě se hodí mít po ruce vodu, misku, známé krmivo a plán krátkých zastavení.</p><p><strong>Zdroj:</strong> <a href="https://www.cd.cz/cestovani-zdravotne-hendikepovanych/-29461/">České dráhy</a></p>',
            ],
        ];
    }
}
