<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"
     xmlns:ror="http://rorweb.com/0.1/">

<channel>
    <title>TourBeez ROR Sitemap</title>
    <link>{{ url('/') }}</link>
    <description>ROR Sitemap for TourBeez Tours</description>

    @foreach($tours as $tour)
        <item>
            <title><![CDATA[{{ $tour->detail->meta_title ?? $tour->title }}]]></title>
            <link>{{ url('/tour/' . $tour->slug) }}</link>
            <description><![CDATA[{{ $tour->detail?->meta_description ?? $tour->detail?->description }}]]></description>

            <ror:about>{{ url('/tour/' . $tour->slug) }}</ror:about>
            <ror:type>Tour</ror:type>
            <ror:updatePeriod>daily</ror:updatePeriod>
        </item>
    @endforeach

</channel>
</rss>
