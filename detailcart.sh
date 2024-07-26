echo "Detail Cart Advanced J2Store"
current_dir="$PWD"
echo "Current Dir $current_dir"
form_folder="$current_dir/mod_j2store_detailcart"
echo "ZIP folder $form_folder"
compress_folder="mod_j2store_detailcart_pack"
copy_folder(){
  move_dir=("css" "language" "tmpl" "helper.php" "index.html" "mod_j2store_detailcart.php" "mod_j2store_detailcart.xml" "script.mod_j2store_detailcart.php")
  pack_compress_folder="$current_dir/$compress_folder"
  if [ -d "$pack_compress_folder" ]
  then
     rm -r "$compress_folder"
     mkdir "$compress_folder"
     for dir in ${move_dir[@]}
     do
       cp -r "$form_folder/$dir" "$pack_compress_folder/$dir"
     done
  else
    mkdir "$compress_folder"
    for dir in ${move_dir[@]}
    do
      cp -r "$form_folder/$dir" "$pack_compress_folder/$dir"
    done
  fi
}
zip_folder(){
  move_dir=("css" "language" "tmpl" "helper.php" "index.html" "mod_j2store_detailcart.php" "mod_j2store_detailcart.xml" "script.mod_j2store_detailcart.php")
  rm "$compress_folder".zip
  zip_folder_name="mod_j2store_detailcart"
  if [ -d "$pack_compress_folder" ]
  then
     cd $compress_folder
     for dir in ${move_dir[@]}
     do
       zip_folder_next="$dir"
       zip -ur "$zip_folder_name".zip $zip_folder_next
     done
  fi
  zip -d "$zip_folder_name".zip __MACOSX/\*
  zip -d "$zip_folder_name".zip \*/.DS_Store
}
copy_folder
zip_folder