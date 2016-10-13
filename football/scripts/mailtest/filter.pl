#!/usr/local/bin/perl

use Digest::SHA1 qw(sha1);
use File::Basename;
use File::Spec;
use File::Path;

use constant HAS_DB_FILE => eval { require DB_File; };

#$fileName = "bayes_toks";
$fileName = "josh";

#tie %h, "DB_File", "fruit", O_RDWR|O_CREAT, 0666 or die "Cannot open file 'fruit': $!\n";

tie %h, "DB_File", $fileName;

#$h{"apple"} = "red" ;
#$h{"orange"} = "orange" ;
#$h{"banana"} = "yellow" ;
#$h{"tomato"} = "red" ;
#$h{"grape"} = "purple";
#$h{"josh2"}{0} = 1;

#$h{"josh2"}{1} = 5;
#$h{"josh2"}{2} = 6;

#$ogleStrip{'a'} = 42;
#$ogleStrip{'g'} = "josh";
#$ogleStrip{'k'} = "yeahaa";

#$h{"ogle"} = %ogleStrip;

while (($k, $v) = each %h) {
    print "$k => $v\n";
}

#@josh = $h{"josh"};
#%uberj = $h{"josh"};
#foreach (@uberj) {
#    print "$_\n";
#}

%jj = $h{"josh2"};
print $jj{0};

#while (($k, $v) = each @josh) {
#    print "$k => $v\n";
#}

untie %h;
