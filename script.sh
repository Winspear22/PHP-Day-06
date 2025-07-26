for i in 04 05 06 07
do
  composer create-project symfony/skeleton ex$i
  cp ex01/composer.json ex$i
  cp ex01/*.sh ex$i
  cd ex$i
  composer require winspear22/php42
  composer require symfony/security-csrf
  composer require symfony/expression-language
  cd ..
done

