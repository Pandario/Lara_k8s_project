# First. From api roots:
npm install; npm run build

# from project root (lara_api/)
docker build -t api_first:local   ./api_first
docker build -t api_middle:local  ./api_middle
docker build -t api_end:local     ./api_end

# after docker images built, from project root:
cd api_first   && composer install && cp .env.example .env && php artisan key:generate && cd ..
cd api_middle  && composer install && cp .env.example .env && php artisan key:generate && cd ..
cd api_end     && composer install && cp .env.example .env && php artisan key:generate && cd ..



# After, from project root:
kubectl apply -f k8s/

## now implement yaml. Project root:
kubectl port-forward svc/api-first   8080:8000   # localhost:8080
kubectl port-forward svc/api-middle  8081:8000   # localhost:8081
kubectl port-forward svc/api-end     8082:8000   # localhost:8082

## php admin panels:
kubectl port-forward svc/phpmyadmin-first   8084:80
kubectl port-forward svc/phpmyadmin-middle  8085:80
kubectl port-forward svc/phpmyadmin-end     8086:80

## after
run all input.sql datasets (in phpmyadmin if prefer)


**Migrate Laravel cache/session/job tables** 
kubectl get pods to get all pods
   _(Do this **inside** the correct Laravel pod for each API):_

   bash
   kubectl exec -it <api-pod-name> -- bash
   php artisan migrate
   php artisan cache:clear
   php artisan config:clear

   ## and if needed
   kubectl rollout restart deployment/<yaml>