# Trasa a časová osa – práce v administraci

Veřejná stránka `/trasa` vzniká ze dvou typů záznamů:

1. **Body trasy** jsou místa: letiště, město, zastávka, cíl nebo aktuální poloha.
2. **Přesuny** propojují dva body: let, autobus, auto, vlak, pěší cesta, kolo nebo loď.

Stejná data řídí mapu i přístupnou časovou osu. Nic se nezadává dvakrát.

## Doporučený postup

Nejprve v **Expedice → Trasa** založte všechna známá místa. Potom v **Expedice → Přesuny** vytvořte spojení mezi nimi.

Příklad:

| Pořadí bodu | Bod | Následující přesun |
|---:|---|---|
| 10 | Praha – letiště | Let Praha → Barcelona |
| 20 | Barcelona – letiště | Autobus Barcelona → Granollers |
| 30 | Granollers | Auto Granollers → Zaragoza |
| 40 | Zaragoza | — |

### Let Praha → Barcelona

- výchozí bod: Praha – letiště,
- cílový bod: Barcelona – letiště,
- doprava: Letadlo,
- způsob vykreslení: Automaticky,
- plánovaný odlet a přílet,
- volitelně dopravce a číslo letu.

Systém vytvoří oblouk po povrchu Země. Let se nikdy neposílá do silničního plánovače.

### Autobus Barcelona → Granollers

- výchozí bod: Barcelona – letiště nebo konkrétní autobusové nádraží,
- cílový bod: Granollers,
- doprava: Autobus,
- způsob vykreslení: Automaticky.

Systém vypočítá orientační silniční trasu. Nezná jízdní řád ani přesnou trasu konkrétní linky. Pokud autobus jede přes konkrétní místo, přidejte je jako průjezdní body.

### Auto Granollers → Zaragoza

- výchozí bod: Granollers,
- cílový bod: Zaragoza,
- doprava: Auto,
- způsob vykreslení: Automaticky,
- případné plánované zastávky zadejte jako průjezdní body nebo jako samostatné body trasy, pokud mají vlastní obsah.

Samostatný bod použijte, pokud se na místě zastavíte, pořídíte fotografie, přidáte video nebo článek. Průjezdní bod použijte pouze pro tvar silniční čáry.

## Vlak, loď, pěší přesun a kolo

Tyto režimy se ve výchozí verzi zobrazí přímou spojnicí přes zadané průjezdní body. Důvod je praktický: veřejné silniční OSRM neposkytuje spolehlivou geometrii konkrétních železničních, lodních nebo pěších spojů.

Pro přesnější tvar přidejte několik průjezdních bodů. Není nutné zadávat desítky bodů; pro reportážní mapu obvykle stačí hlavní změny směru.

## Plán versus skutečnost

Každý přesun má:

- plánovaný odjezd/odlet a příjezd/přílet,
- skutečný odjezd/odlet a příjezd/přílet,
- stav Plánujeme, Právě cestujeme nebo Dokončeno.

Časová osa zobrazuje skutečný čas, pokud je vyplněný; jinak plánovaný. U skutečného času přidá označení „skutečnost“.

Na mapě:

- dokončený přesun je plná čára,
- probíhající přesun je silnější plná čára,
- plánovaný přesun je přerušovaná čára,
- barva rozlišuje dopravní prostředek.

## Fotografie, video a články

Média lze přidat jak k bodu, tak k přesunu:

- fotografie z Barcelony patří k bodu Barcelona,
- fotografie z letadla nebo video z jízdy patří k přesunu,
- každý záznam lze propojit s publikovaným článkem.

Alternativní text fotografie popisuje její sdělení člověku, který ji nevidí. Mapa není jediným nositelem informace; vše je zobrazené také v chronologické HTML časové ose.

## Rychlá aktualizace z telefonu

Stránka `/admin/trasa/rychle-pridat` vytvoří nový bod s aktuální GPS polohou. Hodí se pro označení „Jsme tady“. Přesun k tomuto bodu následně doplňte v administraci v sekci Přesuny.

## Přepočítání čáry

Po změně výchozího bodu, cílového bodu nebo průjezdních bodů se geometrie přepočítá při uložení. V editaci je také samostatné tlačítko **Přepočítat trasu**.

Pokud je externí směrování dočasně nedostupné, úsek zůstane uložený jako orientační spojnice. Později jej lze přepočítat bez ztráty textů, časů nebo médií.
