use Archive::Zip qw( :ERROR_CODES :CONSTANTS );

my $zip = Archive::Zip->new('data/myzip.zip');
my $member1 = $zip->removeMember('indstats.nfl');
print $member1;
