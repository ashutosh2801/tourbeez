<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"
     xmlns:ror="http://rorweb.com/0.1/">

<channel>
    <title>TourBeez ROR Sitemap</title>
    <link>{{ url('/') }}</link>
    <description>ROR Sitemap for TourBeez Pages</description>

    @foreach($pages as $p)
        <item>
            <title><![CDATA[{{ $p->title }}]]></title>
            <link>{{ url($p->href) }}</link>
            <description><![CDATA[{{ $p->description }}]]></description>

            <ror:about>{{ url($p->href) }}</ror:about>
            <ror:type>Tour</ror:type>
            <ror:updatePeriod>daily</ror:updatePeriod>
        </item>
    @endforeach

</channel>
</rss>
