Nette hace
==========

### Demonstration of webapp development using containers and manual (by hands) deployment into Kubernetes.

**note:**
*Don't use Kubernetes for development! Use `docker-compose` for that.*

This project represents a simple CRUD webapplication. In fact there are two webapps - classical MVC with server-side templates and framework. This MVC webapp also have a REST endpoint. Second webapp is a SPA that consumes that REST endpoint and provides alternative interface for our CRUD webapp.

![stack](https://i.postimg.cc/nhpX0xmb/stack.png)

**MVC** (in this example **MVP** - Model View Presenter) webapp is written in [Nette framework](https://github.com/nette). **SPA** is written in [Next.js](https://nextjs.org). Both applications are written honoring [Cloud&nbsp;native philosophy and 12&nbsp;factor&nbsp;app design methodology](https://www.cuelogic.com/blog/12-factor-design-methodology-and-cloud-native-applications). All logs and errors are forwarded to `stdout`, `stderr`. Everything is running as a single process inside containers. 

Dockerfile(s) for developement (`docker-compose`) and production (Kubernetes) are the same. Only ENV vars and `build-args` are used to modify how containers behave in different environments. For example the `IS_PROD_BUILD` is overriden during development so the main Dockerfile will install Xdebug into the docker image.

**Default `ARG` values in Dockerfiles always represents the production! Use `docker-compose.yml` to override during development!**

Please note that this is not an example of best practises of how webapps are created in Nette or Next.js!


how to run (development)
------------------------

1. clone the project
2. copy `.env [example]` into `.env` in the root of the project (change values as needed)
3. setup your `/etc/hosts` (or [use local DNS server](https://passingcuriosity.com/2013/dnsmasq-dev-osx/)) to translate `www.hace.local` and `spa.hace.local` to `127.0.0.1`
4. run `docker-compose up --build`

Applications are now available at `http://www.hace.local:8000` and `http://spa.hace.local:8000`. Feel free to modify sources, your changes will applied immediatelly (no rebuild needed) thanks to clever use of docker volumes.

how to deploy to Kubernetes
---------------------------

#### notes:

- I will asume you are running K8S cluster locally (`kubectl get nodes` returns nodes on your local machine)
- I will assume you have configured [NGINX Ingress Controller](https://kubernetes.github.io/ingress-nginx/deploy/). We don't use Traefik in Kubernetes deployment.
- I will assume you have a docker registry running locally. Kubernetes cannot pick local docker images, you must use registry. You can run local registry like this:

  ```
  docker run -d -p 5000:5000 --name registry --restart always registry:2
  ```

#### build docker images for kubernetes deployment

As mentioned above, Kubernetes cannot pick local docker images (listed by `docker images`) for pods. Kubernetes can only pull images from some registry. So we must build/tag this images and push them to the registry.

There is a handy script for that - [`build-for-kube.sh`](build-for-kube.sh). Run this script to build production variants of docker images and push them to the registry. Note that this script consumes your `.env` file.

```
./build-for-kube.sh
```


#### deployment

Deployment must be done in certain order. For example we cannot deploy `webserver` service first, because it will try to connect to the `app` service which don't exist yet and it will fail. It can be said that we must deploy webapps from the back to the front:

##### 1. setup namespace

run the first kubernetes configuration:

```
kubectl apply -f .kubernetes/00_namespaces.yml
```

You can verify that namespace was created correctly:

```
$ kubectl get namespaces
NAME            STATUS   AGE
default         Active   23d
docker          Active   23d
hace            Active   6s
ingress-nginx   Active   1h
kube-public     Active   23d
kube-system     Active   23d
```

##### 2. setup persistent-volume(s)

Since we are using local Kubernetes cluster (see note above) we must create an empty folder to hold data for our PV:

```
mkdir /tmp/database-hace
```

Now we can setup a persistent-volume:

```
kubectl apply -f .kubernetes/10_persistent-volumes.yml
```

You can always inspect kubernetes object like this:

```
$ kubectl describe pv database
Name:            database
Labels:          <none>
Annotations:     kubectl.kubernetes.io/last-applied-configuration:
                   {"apiVersion":"v1","kind":"PersistentVolume","metadata":{"annotations":{},"name":"database"},"spec":{"accessModes":["ReadWriteOnce"],"capa...
Finalizers:      [kubernetes.io/pv-protection]
StorageClass:    local
Status:          Available
Claim:
Reclaim Policy:  Retain
Access Modes:    RWO
Capacity:        500Mi
Node Affinity:   <none>
Message:
Source:
    Type:          HostPath (bare host directory volume)
    Path:          /tmp/database-hace
    HostPathType:  DirectoryOrCreate
Events:            <none>
```

_**Note:** to persist the data, use some other folder as `/tmp` to hold your PV. Change the above configuration file accordingly._

##### 3. deploy the `database` service

Now that our database have a space to persist its data, we can deploy the `database` service:

```
kubectl apply -f .kubernetes/20_database.yml
```

Make sure that `database` was deployed correctly and that pod is running:

```
$ kubectl get all --namespace hace
NAME                           READY   STATUS    RESTARTS   AGE   LABELS
pod/database-d969547d6-xftx7   1/1     Running   0          34s   app=database,pod-template-hash=852510382

NAME               TYPE        CLUSTER-IP      EXTERNAL-IP   PORT(S)    AGE   LABELS
service/database   ClusterIP   10.105.80.232   <none>        5432/TCP   34s   <none>

NAME                       DESIRED   CURRENT   UP-TO-DATE   AVAILABLE   AGE   LABELS
deployment.apps/database   1         1         1            1           34s   <none>

NAME                                 DESIRED   CURRENT   READY   AGE   LABELS
replicaset.apps/database-d969547d6   1         1         1       34s   app=database,pod-template-hash=852510382
```

In case of problems alwasy use `describe` to inspect the pod:

```
$ kubectl describe pod --namespace hace database-d969547d6-xftx7
Name:           database-d969547d6-xftx7
Namespace:      hace
Node:           docker-for-desktop/192.168.65.3
Start Time:     Wed, 24 Oct 2018 18:51:17 +0200
Labels:         app=database
                pod-template-hash=852510382
...

Events:
  Type    Reason                 Age    From                         Message
  ----    ------                 ----   ----                         -------
  Normal  Scheduled              2m43s  default-scheduler            Successfully assigned database-d969547d6-xftx7 to docker-for-desktop
  Normal  SuccessfulMountVolume  2m43s  kubelet, docker-for-desktop  MountVolume.SetUp succeeded for volume "database"
  Normal  SuccessfulMountVolume  2m43s  kubelet, docker-for-desktop  MountVolume.SetUp succeeded for volume "default-token-54w26"
  Normal  Pulled                 2m42s  kubelet, docker-for-desktop  Container image "postgres:11-alpine" already present on machine
  Normal  Created                2m42s  kubelet, docker-for-desktop  Created container
  Normal  Started                2m42s  kubelet, docker-for-desktop  Started container
```

##### 4. run database migrations

We cannot use `kubectl apply` to run our configuration file for migration, since we are using a very handy `generateName` directive in it. That way we can run migration again and again. Without `generateName` the kubernetes object `Job` cannot be reruned.

To run the migrations we must use:

```
kubectl create -f .kubernetes/30_migration.yml
```

Make sure that pod for migrations is in `Completed` status:

```
$ kubectl get all --namespace hace
NAME                                   READY   STATUS      RESTARTS   AGE
pod/database-d969547d6-xftx7           1/1     Running     0          7m
pod/database-migrations.0kj7t8-xk2rk   0/1     Completed   0          1m

NAME               TYPE        CLUSTER-IP      EXTERNAL-IP   PORT(S)    AGE
service/database   ClusterIP   10.105.80.232   <none>        5432/TCP   7m

NAME                       DESIRED   CURRENT   UP-TO-DATE   AVAILABLE   AGE
deployment.apps/database   1         1         1            1           7m

NAME                                 DESIRED   CURRENT   READY   AGE
replicaset.apps/database-d969547d6   1         1         1       7m

NAME                                   DESIRED   SUCCESSFUL   AGE
job.batch/database-migrations.0kj7t8   1         1            1m
```

##### 5. deploy the `app` service and the `webserver` service

```
kubectl apply -f .kubernetes/40_app.yml
kubectl apply -f .kubernetes/40_webserver.yml
```

In addition to pods & services we also deployed an ingres object:

```
$ kubectl get ingresses --namespace hace
NAME        HOSTS            ADDRESS     PORTS   AGE
webserver   www.hace.local   localhost   80      2m

$ kubectl describe ingress --namespace hace webserver
Name:             webserver
Namespace:        hace
Address:
Default backend:  default-http-backend:80 (<none>)
Rules:
  Host            Path  Backends
  ----            ----  --------
  www.hace.local
                  /   webserver:8000 (<none>)
Annotations:
  kubectl.kubernetes.io/last-applied-configuration:  {"apiVersion":"extensions/v1beta1","kind":"Ingress","metadata":{"annotations":{"kubernetes.io/ingress.class":"nginx"},"name":"webserver","namespace":"hace"},"spec":{"rules":[{"host":"www.hace.local","http":{"paths":[{"backend":{"serviceName":"webserver","servicePort":8000},"path":"/"}]}}]}}

  kubernetes.io/ingress.class:  nginx
Events:
  Type    Reason  Age   From                      Message
  ----    ------  ----  ----                      -------
  Normal  CREATE  7s    nginx-ingress-controller  Ingress hace/webserver
```

Now it is time to see your webapp running in Kubernetes. Navigate your browser to `http://www.hace.local`:

![MVP running in Kubernetes](https://i.postimg.cc/pd8GjdxY/Screen-Shot-2018-10-24www.png)

##### 6. deploy the `spa` service

Finally lets deploy the `spa` service to demonstrate how ingress can route to different pod by domain name rule:

```
kubectl apply -f .kubernetes/50_spa.yml
```

```
$ kubectl get ingresses --namespace hace
NAME        HOSTS             ADDRESS     PORTS   AGE
spa         spa.hace.local                80      2s
webserver   www.hace.local    localhost   80      7m
```

![SPA running in Kubernets](https://i.postimg.cc/T2ZZpBr6/Screen-Shot-2018-10-24spa.png)

extra notes
-----------

- there are some extra services in the stack during development
  - `adminer` - webinterface to manage database. Access it via `adminer.hace.local:8000`, login credentials are:

    ```
    system: PostgreSQL
    server: database
    username: postgres
    password: secret
    ```

  - static code analyzers - they are run once at the boot of the stack in development (`docker-composer up`). You can run them manually:

    ```
    docker-compose run --rm parallel-lint
    docker-compose run --rm phpcs
    docker-compose run --rm phpstan
    ```
- there is a `presentations` folder in this project - a presentation(s) I gave about this topic at various meetup(s).


license
-------

MIT
