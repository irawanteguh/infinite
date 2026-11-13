<?php
namespace Modules\Pe\Libraries;

class ReportCharts
{
    private static function escape($s): string {return htmlspecialchars(str_replace(['–','—'],['-','-'],(string)$s),ENT_QUOTES|ENT_XML1,'UTF-8');}

    public static function bars(array $items, float $maximum, string $unit = '', int $labelWidth = 200): string
    {
        if(!$items)return '<p class="note">Belum ada data untuk grafik.</p>';
        $rows=[];$y=12;$max=max(1,$maximum);$plot=630-$labelWidth-75;
        foreach($items as $i=>$item){
            $label=wordwrap((string)$item['label'], max(16,(int)($labelWidth/6.1)),"\n",true);$lines=explode("\n",$label);$height=max(29,count($lines)*13+9);
            $center=$y+$height/2;$color=$item['color']??['#087e82','#68a991','#729caf','#a3b7ad'][$i%4];
            $svg='';foreach($lines as $j=>$line)$svg.='<text x="0" y="'.($y+13+$j*13).'" font-size="10" fill="#476970">'.self::escape($line).'</text>';
            $value=$item['value'];$width=$value===null?0:max(0,min($plot,$value/$max*$plot));
            $svg.='<rect x="'.$labelWidth.'" y="'.($center-6).'" width="'.$plot.'" height="12" rx="3" fill="#edf3f0"/>';
            $svg.='<rect x="'.$labelWidth.'" y="'.($center-6).'" width="'.$width.'" height="12" rx="3" fill="'.$color.'"/>';
            $text=$value===null?'-':number_format($value,$unit==='%'||$unit===' / 4'||$unit===' / 5'?1:0,',','.').$unit;
            $svg.='<text x="'.($labelWidth+$plot+8).'" y="'.($center+4).'" font-size="10" fill="#24454d">'.self::escape($text).'</text>';
            $rows[]=$svg;$y+=$height;
        }
        return '<svg xmlns="http://www.w3.org/2000/svg" width="630" height="'.($y+8).'" viewBox="0 0 630 '.($y+8).'">'.implode('',$rows).'</svg>';
    }

    public static function cloud(array $words): string
    {
        if (!$words) return '<p class="note">Belum ada saran tertulis.</p>';
        $maximum=max(array_column($words,'count'));$lines=[];$line=[];$width=0;$height=0;
        foreach(array_slice($words,0,30) as $i=>$word){
            $font=12+round(17*sqrt($word['count']/$maximum));
            $w=min(600,mb_strlen($word['word'],'UTF-8')*$font*.58+22);
            if($width+$w>600&&$line){$lines[]=[$line,$width,$height];$line=[];$width=0;$height=0;}
            $line[]=['word'=>$word['word'],'font'=>$font,'width'=>$w,'color'=>['#087e82','#559887','#628da8','#84988b'][$i%4]];
            $width+=$w;$height=max($height,$font+20);
        }
        if($line)$lines[]=[$line,$width,$height];$y=10;$body='';
        foreach($lines as [$items,$width,$height]){$x=(630-$width)/2;$y+=$height;foreach($items as $item){$body.='<text x="'.$x.'" y="'.$y.'" font-size="'.$item['font'].'" fill="'.$item['color'].'">'.self::escape($item['word']).'</text>';$x+=$item['width'];}}
        return '<svg xmlns="http://www.w3.org/2000/svg" width="630" height="'.($y+20).'" viewBox="0 0 630 '.($y+20).'">'.$body.'</svg>';
    }

    public static function trend(array $monthly, int $scale): string
    {
        $svg='<svg xmlns="http://www.w3.org/2000/svg" width="630" height="215" viewBox="0 0 630 215">';
        for($v=1;$v<=$scale;$v++){$y=165-($v-1)/($scale-1)*130;$svg.='<line x1="30" x2="610" y1="'.$y.'" y2="'.$y.'" stroke="#e1eae7"/><text x="10" y="'.($y+4).'" font-size="10">'.$v.'</text>';}
        $n=count($monthly);$step=$n>1?560/($n-1):0;
        foreach(array_keys($monthly) as $i=>$month)$svg.='<text x="'.(26+$i*$step).'" y="185" font-size="9">'.substr($month,5,2).'</text>';
        foreach(['IGD'=>'#087e82','RJ'=>'#729caf','RI'=>'#68a991'] as $code=>$color){$previous=null;foreach(array_values($monthly) as $i=>$rows){$value=$rows[$code]['average'];if($value===null){$previous=null;continue;}$x=35+$i*$step;$y=165-($value-1)/($scale-1)*130;if($previous)$svg.='<line x1="'.$previous[0].'" y1="'.$previous[1].'" x2="'.$x.'" y2="'.$y.'" stroke="'.$color.'" stroke-width="2"/>';$svg.='<circle cx="'.$x.'" cy="'.$y.'" r="3" fill="'.$color.'"/>';$previous=[$x,$y];}}
        return $svg.'<text x="30" y="207" font-size="10" fill="#087e82">IGD</text><text x="130" y="207" font-size="10" fill="#729caf">Rawat Jalan</text><text x="270" y="207" font-size="10" fill="#68a991">Rawat Inap</text></svg>';
    }
}
