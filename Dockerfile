FROM ubuntu:16.04

MAINTAINER Mohamed Ibrahim Version: 0.1

ADD ./envprep.sh /tmp/envprep.sh

RUN /bin/sh /tmp/envprep.sh
