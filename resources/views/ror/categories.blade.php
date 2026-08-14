<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"
     xmlns:ror="http://rorweb.com/0.1/">

<channel>
    <title>TourBeez ROR Sitemap</title>
    <link>{{ url('/') }}</link>
    <description>ROR Sitemap for TourBeez Categories</description>

    @foreach($categories as $c)
        <item>
            <title><![CDATA[{{ 'Top Things to Do in '.$c->name.' Tours & Attractions | TourBeez' }}]]></title>
            <link>{{ url('/things-to-do-in-' . \Str::slug($c->name).'/'.$c->id.'-c3') }}</link>
            <description><![CDATA[{{ 'Enjoy unforgettable experiences in '.$c->name.'. Explore tours, attractions & activities with TourBeez. Reserve your perfect '.$c->name.' trip today.' }}]]></description>

            <ror:about>{{ url('/things-to-do-in-' . \Str::slug($c->name).'/'.$c->id.'-c3') }}</ror:about>
            <ror:type>Tour Category</ror:type>
            <ror:updatePeriod>daily</ror:updatePeriod>
        </item>
    @endforeach

</channel>
</rss>
