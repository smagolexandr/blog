echo;
echo "what you wanna do?"
echo "1 - install project"
echo "2 - install bootstrap"
echo "0 - exit"

read key

case "$key" in
   "1" )
      composer install
   ;;

   "2" )
     cp -r ./vendor/twbs/bootstrap/dist/* ./web/
  ;;
   "0" ) ;;
esac